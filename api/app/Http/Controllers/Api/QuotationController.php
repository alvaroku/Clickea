<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Quotation;
use App\Models\RequestedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * Get quotations for a specific service request (for Client).
     */
    public function index(RequestedService $serviceRequest)
    {
        if ($serviceRequest->client_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $quotations = $serviceRequest->quotations()->with('provider')->get();

        return response()->json([
            'data' => $quotations
        ]);
    }

    /**
     * Provider sends a quotation (price and observations).
     */
    public function update(Request $request, Quotation $quotation)
    {
        if ($quotation->provider_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'price' => 'required|numeric|min:0',
            'provider_observations' => 'nullable|string'
        ]);

        $quotation->update([
            'price' => $request->price,
            'provider_observations' => $request->provider_observations,
            'status' => 'cotizada'
        ]);

        // Create notification for the client
        $serviceRequest = $quotation->serviceRequest;
        $service = $serviceRequest->service;
        $provider = $quotation->provider;

        Notification::create([
            'user_id' => $serviceRequest->client_id,
            'title' => 'Nueva Cotización Recibida',
            'message' => "Has recibido una nueva cotización de {$provider->name} para el servicio {$service->name} por $" . number_format($request->price, 2) . ".",
            'additional_data' => [
                'type' => 'quotation',
                'quotation_id' => $quotation->id,
                'service_request_id' => $serviceRequest->id,
                'service_name' => $service->name ?? '',
                'provider_name' => $provider->name,
                'price' => $request->price,
                'date' => $serviceRequest->date,
                'time' => $serviceRequest->time,
            ],
        ]);

        return response()->json([
            'message' => 'Cotización enviada exitosamente.',
            'data' => $quotation
        ]);
    }

    /**
     * Client accepts a quotation.
     */
    public function accept(Quotation $quotation)
    {
        $serviceRequest = $quotation->serviceRequest;

        if ($serviceRequest->client_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($quotation->status !== 'cotizada') {
            return response()->json(['message' => 'Solo se pueden aceptar cotizaciones enviadas.'], 422);
        }

        DB::transaction(function () use ($quotation, $serviceRequest) {
            // Update the accepted quotation
            $quotation->update(['status' => 'aceptada']);

            // Reject all other quotations for the same request
            $serviceRequest->quotations()
                ->where('id', '!=', $quotation->id)
                ->update(['status' => 'rechazada']);

            // Update request status
            $serviceRequest->update(['status' => 'contratada']);
        });

        return response()->json([
            'message' => 'Cotización aceptada correctamente.'
        ]);
    }

    /**
     * Client rejects a specific quotation.
     */
    public function reject(Quotation $quotation)
    {
        $serviceRequest = $quotation->serviceRequest;

        if ($serviceRequest->client_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (!in_array($quotation->status, ['pendiente', 'cotizada'])) {
            return response()->json(['message' => 'Solo se pueden rechazar cotizaciones pendientes o enviadas.'], 422);
        }

        $quotation->update(['status' => 'rechazada']);

        return response()->json([
            'message' => 'Cotización rechazada.'
        ]);
    }

    /**
     * Client rates a quotation (after service is finished).
     */
    public function rate(Request $request, Quotation $quotation)
    {
        $serviceRequest = $quotation->serviceRequest;

        if ($serviceRequest->client_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($quotation->status !== 'aceptada') {
            return response()->json(['message' => 'Solo se puede calificar un servicio aceptado.'], 422);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'rating_comment' => 'nullable|string'
        ]);

        $quotation->update([
            'rating' => $request->rating,
            'rating_comment' => $request->rating_comment
        ]);

        return response()->json([
            'message' => 'Calificación enviada correctamente.',
            'data' => $quotation
        ]);
    }
}
