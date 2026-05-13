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
use OpenApi\Attributes as OA;

class CategoriaController extends Controller
{
    #[OA\Get(
        path: '/categorias',
        summary: 'Listar todas las categorías',
        description: 'Endpoint público. Devuelve todas las categorías disponibles.',
        tags: ['Categorías'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de categorías',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nombre', type: 'string', example: 'Sofás'),
                                new OA\Property(property: 'descripcion', type: 'string', example: 'Sofás y sillones'),
                            ]
                        )),
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        $categorias = Categoria::all();

        return new CategoriaCollection($categorias);
    }

    #[OA\Get(
        path: '/categorias/{id}',
        summary: 'Ver una categoría por ID',
        description: 'Endpoint público. Devuelve la categoría con sus muebles asociados.',
        tags: ['Categorías'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos de la categoría con sus muebles'),
            new OA\Response(response: 404, description: 'Categoría no encontrada'),
        ]
    )]
    public function show(Categoria $categoria)
    {
        return new CategoriaResource($categoria->loadMissing('muebles'));
    }

    #[OA\Post(
        path: '/categorias',
        summary: 'Crear una nueva categoría',
        description: 'Requiere token con ability `muebles.crear` (rol Administrador o Gestor).',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Mesas'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Mesas de comedor y auxiliares'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Categoría creada correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permisos'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'data'    => new CategoriaResource($categoria),
        ], 201);
    }

    #[OA\Put(
        path: '/categorias/{id}',
        summary: 'Actualizar una categoría',
        description: 'Requiere token con ability `muebles.editar` (rol Administrador o Gestor).',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Mesas de comedor'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Descripción actualizada'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Categoría actualizada correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permisos'),
            new OA\Response(response: 404, description: 'Categoría no encontrada'),
        ]
    )]
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'data'    => new CategoriaResource($categoria),
        ]);
    }

    #[OA\Delete(
        path: '/categorias/{id}',
        summary: 'Eliminar una categoría',
        description: 'Requiere token con ability `muebles.eliminar` (rol Administrador o Gestor).',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Categoría eliminada correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permisos'),
            new OA\Response(response: 404, description: 'Categoría no encontrada'),
        ]
    )]
    public function destroy(DeleteCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
}
