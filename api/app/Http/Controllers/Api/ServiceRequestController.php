<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\RequestedService;
use App\Models\Quotation;
use App\Models\User;
use App\Models\File as FileModel;
use App\Mail\ServiceRequestedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RequestedService::with(['service.category', 'image'])
            ->withCount('quotations')
            ->where('client_id', $request->user()->id);

        return $this->applyFiltersAndResponse($request, $query);
    }

    /**
     * Display a listing of services for the provider.
     */
    public function indexForProvider(Request $request)
    {
        // Now providers see their Quotations
        $query = Quotation::with(['serviceRequest.service.category', 'serviceRequest.client', 'serviceRequest.image'])
            ->where('provider_id', $request->user()->id);

        // We need to adapt applyFiltersAndResponse for Quotation or implement it here
        return $this->applyQuotationFiltersAndResponse($request, $query);
    }

    protected function applyFiltersAndResponse(Request $request, $query)
    {
        // Filter by search text (service name or description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('service', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('service', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by validity
        if ($request->filled('validity_status')) {
            $query->where('validity_status', $request->validity_status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $requests
        ]);
    }

    protected function applyQuotationFiltersAndResponse(Request $request, $query)
    {
        // Filter by search text (service name or description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('serviceRequest.service', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('serviceRequest.service', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by status (Quotation status)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by validity (Service Request date)
        if ($request->filled('validity_status')) {
            $query->whereHas('serviceRequest', function($q) use ($request) {
                $q->where('validity_status', $request->validity_status);
            });
        }

        $quotations = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $quotations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'time' => 'required',
            'location' => 'required|string',
            'observations' => 'nullable|string',
            'reference_image' => 'nullable|image|max:5120', // Max 5MB
        ]);

        $originalService = Service::findOrFail($request->service_id);

        // Find all active providers for this service name
        $providers = Service::where('name', $originalService->name)
            ->where('active', true)
            ->pluck('user_id')
            ->unique();

        if ($providers->isEmpty()) {
            return response()->json([
                'message' => 'No hay proveedores disponibles para este servicio en este momento.'
            ], 422);
        }

        $filePath = null;
        $fileData = null;

        // Handle file upload once
        if ($request->hasFile('reference_image')) {
            $file = $request->file('reference_image');
            $filePath = $file->store('reference_images', 'public');
            $fileData = [
                'path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        $requestedService = DB::transaction(function () use ($request, $providers, $fileData) {
            // 1. Create the single RequestedService
            $serviceRequest = RequestedService::create([
                'client_id' => $request->user()->id,
                'service_id' => $request->service_id,
                'date' => $request->date,
                'time' => $request->time,
                'location' => $request->location,
                'observations' => $request->observations,
                'status' => 'pendiente',
                'validity_status' => 'vigente',
            ]);

            // Attach file record if exists
            if ($fileData) {
                $serviceRequest->image()->create($fileData);
            }

            // 2. Create Quotations for each provider
            foreach ($providers as $providerId) {
                Quotation::create([
                    'service_request_id' => $serviceRequest->id,
                    'provider_id' => $providerId,
                    'status' => 'pendiente',
                ]);

                // Send notification
                try {
                    $provider = User::find($providerId);
                    if ($provider) {
                        Mail::to($provider->email)->send(new ServiceRequestedNotification($serviceRequest));
                    }
                } catch (\Exception $e) {
                    Log::error("Error sending email to provider {$providerId}: " . $e->getMessage());
                }
            }

            return $serviceRequest;
        });

        return response()->json([
            'message' => 'Solicitud enviada exitosamente a ' . count($providers) . ' proveedores.',
            'data' => $requestedService->load('quotations')
        ], 201);
    }

    public function cancel(RequestedService $requestedService)
    {
        $this->authorizeOwner($requestedService);

        // Can cancel if 'pendiente' or ('contratada' and still valid)
        if (!in_array($requestedService->status, ['pendiente', 'contratada', 'cotizada'])) {
            return response()->json(['message' => 'No se puede cancelar en este estado.'], 422);
        }

        // Logic for 'valid' check for 'contratada'
        if ($requestedService->status === 'contratada') {
            $appointmentDate = \Carbon\Carbon::parse($requestedService->date . ' ' . $requestedService->time);
            if ($appointmentDate->isPast()) {
                return response()->json(['message' => 'No se puede cancelar un servicio cuya fecha ya pasó.'], 422);
            }
        }

        $requestedService->update(['status' => 'cancelada']);

        return response()->json(['message' => 'Solicitud cancelada correctamente.']);
    }

    public function accept(RequestedService $requestedService)
    {
        $this->authorizeOwner($requestedService);

        if ($requestedService->status !== 'cotizada') {
            return response()->json(['message' => 'Solo se pueden aceptar solicitudes cotizadas.'], 422);
        }

        $requestedService->update(['status' => 'contratada']);

        return response()->json(['message' => 'Solicitud aceptada correctamente.']);
    }

    public function reject(RequestedService $requestedService)
    {
        $this->authorizeOwner($requestedService);

        if ($requestedService->status !== 'cotizada') {
            return response()->json(['message' => 'Solo se pueden rechazar solicitudes cotizadas.'], 422);
        }

        $requestedService->update(['status' => 'rechazada']);

        return response()->json(['message' => 'Solicitud rechazada correctamente.']);
    }

    /**
     * Provider actions
     */
    public function quote(Request $request, RequestedService $requestedService)
    {
        $this->authorizeProvider($requestedService);

        if ($requestedService->status !== 'pendiente') {
            return response()->json(['message' => 'Solo se pueden cotizar solicitudes pendientes.'], 422);
        }

        $request->validate([
            'price' => 'required|numeric|min:0',
            'provider_observations' => 'nullable|string',
        ]);

        $requestedService->update([
            'price' => $request->price,
            'provider_observations' => $request->provider_observations,
            'status' => 'cotizada',
        ]);

        return response()->json(['message' => 'Cotización enviada correctamente.']);
    }

    public function rejectByProvider(RequestedService $requestedService)
    {
        $this->authorizeProvider($requestedService);

        if ($requestedService->status !== 'pendiente') {
            return response()->json(['message' => 'Solo se pueden rechazar solicitudes pendientes.'], 422);
        }

        $requestedService->update(['status' => 'rechazada']);

        return response()->json(['message' => 'Solicitud rechazada correctamente.']);
    }

    public function rate(Request $request, RequestedService $requestedService)
    {
        $this->authorizeOwner($requestedService);

        if ($requestedService->status !== 'contratada') {
            return response()->json(['message' => 'Solo se pueden calificar servicios contratados.'], 422);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'rating_comment' => 'nullable|string'
        ]);

        $requestedService->update([
            'rating' => $request->rating,
            'rating_comment' => $request->rating_comment,
        ]);

        return response()->json(['message' => 'Servicio calificado correctamente.']);
    }

    protected function authorizeOwner(RequestedService $requestedService)
    {
        if ($requestedService->client_id !== Auth::id()) {
            abort(403, 'No tienes permiso para modificar esta solicitud.');
        }
    }

    protected function authorizeProvider(RequestedService $requestedService)
    {
        if ($requestedService->provider_id !== Auth::id()) {
            abort(403, 'No tienes permiso para gestionar esta solicitud.');
        }
    }
}
