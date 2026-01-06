<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => Product::all(),
            'message' => 'Productos listados exitosamente.'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric'
        ]);

        $product = Product::create($data);

        return response()->json([
            'data' => $product,
            'message' => 'Producto creado exitosamente.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'data' => $product,
            'message' => 'Producto obtenido exitosamente.'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric'
        ]);

        $product->update($data);

        return response()->json([
            'data' => $product,
            'message' => 'Producto actualizado exitosamente.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'data' => null,
            'message' => 'Producto eliminado exitosamente.'
        ]);
    }
}
