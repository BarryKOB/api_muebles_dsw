<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Http\Requests\DeleteCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Http\Resources\CategoriaCollection;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     * Público: no requiere token.
     */
    public function index()
    {
        $categorias = Categoria::all();

        return new CategoriaCollection($categorias);
    }

    /**
     * Display the specified resource.
     * Público: no requiere token.
     */
    public function show(Categoria $categoria)
    {
        // Cargamos los muebles de esta categoría
        return new CategoriaResource($categoria->loadMissing('muebles'));
    }

    /**
     * Store a newly created resource in storage.
     * Protegido: requiere token con ability 'muebles.crear'
     */
    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'data'    => new CategoriaResource($categoria),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     * Protegido: requiere token con ability 'muebles.editar'
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'data'    => new CategoriaResource($categoria),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Protegido: requiere token con ability 'muebles.eliminar'
     */
    public function destroy(DeleteCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
}
