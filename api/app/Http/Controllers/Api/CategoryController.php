<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('active', $request->status === 'active');
        }

        $categories = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function all()
    {
        $categories = Category::where('active', true)->orderBy('name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría creada con éxito',
            'data' => $category
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría actualizada con éxito',
            'data' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Categoría eliminada con éxito'
        ]);
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['active' => !$category->active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Estado de la categoría actualizado',
            'data' => $category
        ]);
    }
}
