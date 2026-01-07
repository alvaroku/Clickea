<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource for the admin/owner.
     */
    public function index(Request $request)
    {
        $query = Service::where('user_id', Auth::id())->with('category');

        // Búsqueda por texto (nombre o descripción)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por estado
        if ($request->has('status') && $request->status !== 'all') {
            $active = $request->status === 'active';
            $query->where('active', $active);
        }

        $services = $query->latest()->paginate(10);

        return response()->json([
            'data' => $services,
            'message' => 'Servicios listados exitosamente.'
        ]);
    }

    /**
     * Display a listing of all active services for clients.
     */
    public function catalog(Request $request)
    {
        $query = Service::where('active', true)->with(['category', 'owner']);

        // Búsqueda por texto
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por género
        if ($request->has('gender') && $request->gender !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('gender', $request->gender)
                  ->orWhere('gender', 'both');
            });
        }

        $services = $query->latest()->paginate(12);

        return response()->json([
            'data' => $services,
            'message' => 'Catálogo obtenido exitosamente.'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'gender' => 'nullable|in:male,female,both'
        ]);

        $service = Service::create([
            ...$validated,
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'data' => $service,
            'message' => 'Servicio creado exitosamente.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        // El dueño puede verlo siempre.
        // Otros pueden verlo si está activo.
        if ($service->user_id !== Auth::id() && !$service->active) {
            return response()->json(['message' => 'Servicio no disponible'], 403);
        }

        return response()->json([
            'data' => $service->load('category'),
            'message' => 'Servicio obtenido exitosamente.'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'gender' => 'nullable|in:male,female,both'
        ]);

        $service->update($validated);

        return response()->json([
            'data' => $service,
            'message' => 'Servicio actualizado exitosamente.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $service->delete();

        return response()->json([
            'message' => 'Servicio eliminado exitosamente.'
        ]);
    }

    /**
     * Toggle the status of the service.
     */
    public function toggleStatus(Service $service)
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $service->active = !$service->active;
        $service->save();

        $status = $service->active ? 'activado' : 'inactivado';

        return response()->json([
            'data' => $service,
            'message' => "Servicio {$status} exitosamente."
        ]);
    }
}

