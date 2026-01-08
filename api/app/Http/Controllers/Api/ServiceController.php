<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource for the admin/owner.
     */
    public function index(Request $request)
    {
        $query = Service::where('user_id', Auth::id())->with(['category', 'images']);

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
        $query = Service::where('active', true)->with(['category', 'owner', 'images']);

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
            'gender' => 'nullable|in:male,female,both',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $service = Service::create([
            ...$validated,
            'user_id' => Auth::id()
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('service_images', 'public');

                File::create([
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'fileable_id' => $service->id,
                    'fileable_type' => Service::class,
                ]);
            }
        }

        return response()->json([
            'data' => $service->load('images'),
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
            'data' => $service->load(['category', 'images']),
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
            'gender' => 'nullable|in:male,female,both',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $service->update($validated);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('service_images', 'public');

                File::create([
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'fileable_id' => $service->id,
                    'fileable_type' => Service::class,
                ]);
            }
        }

        return response()->json([
            'data' => $service->load('images'),
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

    /**
     * Delete a specific image from a service.
     */
    public function deleteImage(Service $service, File $file)
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Verify the file belongs to this service
        if ($file->fileable_id !== $service->id || $file->fileable_type !== Service::class) {
            return response()->json(['message' => 'Imagen no pertenece a este servicio'], 403);
        }

        // Delete from storage
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Delete from database
        $file->delete();

        return response()->json([
            'message' => 'Imagen eliminada exitosamente.'
        ]);
    }
}

