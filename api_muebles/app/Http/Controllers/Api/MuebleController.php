<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMuebleRequest;
use App\Http\Requests\UpdateMuebleRequest;
use App\Http\Requests\DeleteMuebleRequest;
use App\Http\Resources\MuebleResource;
use App\Http\Resources\MuebleCollection;
use App\Models\Mueble;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Muebles',
    version: '1.0.0',
    description: 'API REST para la gestión de muebles y categorías de Habita'
)]
#[OA\Server(
    url: 'http://localhost:5502/api/v1',
    description: 'Servidor local'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token Bearer obtenido desde la API de Usuarios al hacer login'
)]
class MuebleController extends Controller
{
    #[OA\Get(
        path: '/muebles',
        summary: 'Listar todos los muebles',
        description: 'Endpoint público. Soporta filtros por categoría, precio, color y material, búsqueda por texto, ordenación y paginación.',
        tags: ['Muebles'],
        parameters: [
            new OA\Parameter(name: 'categoria', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'precio_min', in: 'query', required: false, schema: new OA\Schema(type: 'number'), example: 100),
            new OA\Parameter(name: 'precio_max', in: 'query', required: false, schema: new OA\Schema(type: 'number'), example: 500),
            new OA\Parameter(name: 'color', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'blanco'),
            new OA\Parameter(name: 'material', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'madera'),
            new OA\Parameter(name: 'buscar', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'sofá'),
            new OA\Parameter(name: 'orden', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['precio_asc', 'precio_desc', 'nombre_asc', 'nombre_desc'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de muebles',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nombre', type: 'string', example: 'Sofá Milano'),
                                new OA\Property(property: 'descripcion', type: 'string', example: 'Sofá de 3 plazas'),
                                new OA\Property(property: 'precio', type: 'number', example: 299.99),
                                new OA\Property(property: 'stock', type: 'integer', example: 10),
                                new OA\Property(property: 'color', type: 'string', example: 'gris'),
                                new OA\Property(property: 'material', type: 'string', example: 'tela'),
                            ]
                        )),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $validated = $request->validate([
            'categoria'    => 'sometimes|nullable|integer|exists:categorias,id',
            'categoria_id' => 'sometimes|nullable|integer|exists:categorias,id',
            'precio_min'   => 'sometimes|nullable|numeric|min:0',
            'precio_max'   => 'sometimes|nullable|numeric|min:0',
            'color'        => 'sometimes|nullable|string|max:100',
            'material'     => 'sometimes|nullable|string|max:100',
            'buscar'       => 'sometimes|nullable|string|max:255',
            'orden'        => 'sometimes|nullable|string|in:precio_asc,precio_desc,nombre_asc,nombre_desc',
            'per_page'     => 'sometimes|nullable|integer|min:1|max:100',
        ]);

        $query = Mueble::with('categoria');

        $categoriaId = $validated['categoria'] ?? $validated['categoria_id'] ?? null;
        if ($categoriaId !== null) {
            $query->where('categoria_id', $categoriaId);
        }

        if (array_key_exists('precio_min', $validated) && $validated['precio_min'] !== null) {
            $query->where('precio', '>=', $validated['precio_min']);
        }
        if (array_key_exists('precio_max', $validated) && $validated['precio_max'] !== null) {
            $query->where('precio', '<=', $validated['precio_max']);
        }

        if (!empty($validated['color'])) {
            $query->where('color', $validated['color']);
        }

        if (!empty($validated['material'])) {
            $query->where('material', $validated['material']);
        }

        if (!empty($validated['buscar'])) {
            $buscar = $validated['buscar'];
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $orden = $validated['orden'] ?? null;
        match ($orden) {
            'precio_asc'  => $query->orderBy('precio', 'asc'),
            'precio_desc' => $query->orderBy('precio', 'desc'),
            'nombre_asc'  => $query->orderBy('nombre', 'asc'),
            'nombre_desc' => $query->orderBy('nombre', 'desc'),
            default       => $query->orderBy('created_at', 'desc'),
        };

        $perPage = (int) ($validated['per_page'] ?? 12);
        if ($perPage < 1) {
            $perPage = 12;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $muebles = $query->paginate($perPage)->appends($request->query());

        return new MuebleCollection($muebles);
    }

    #[OA\Get(
        path: '/muebles/{id}',
        summary: 'Ver un mueble por ID',
        description: 'Endpoint público. Devuelve el mueble con su categoría e imágenes.',
        tags: ['Muebles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Datos del mueble'),
            new OA\Response(response: 404, description: 'Mueble no encontrado'),
        ]
    )]
    public function show(Mueble $mueble)
    {
        return new MuebleResource($mueble->loadMissing(['categoria', 'imagenes']));
    }

    #[OA\Post(
        path: '/muebles',
        summary: 'Crear un nuevo mueble',
        description: 'Requiere token con ability `muebles.crear` (rol Administrador o Gestor).',
        tags: ['Muebles'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'precio', 'stock', 'categoria_id'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Sofá Milano'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Sofá de 3 plazas en tela gris'),
                    new OA\Property(property: 'precio', type: 'number', example: 299.99),
                    new OA\Property(property: 'stock', type: 'integer', example: 10),
                    new OA\Property(property: 'color', type: 'string', example: 'gris'),
                    new OA\Property(property: 'material', type: 'string', example: 'tela'),
                    new OA\Property(property: 'categoria_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Mueble creado correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permisos'),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function store(StoreMuebleRequest $request)
    {
        $mueble = Mueble::create($request->validated());

        return response()->json([
            'message' => 'Mueble creado correctamente',
            'data'    => new MuebleResource($mueble->load('categoria')),
        ], 201);
    }

    #[OA\Put(
        path: '/muebles/{id}',
        summary: 'Actualizar un mueble',
        description: 'Requiere token con ability `muebles.editar` (rol Administrador o Gestor).',
        tags: ['Muebles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Sofá Milano v2'),
                    new OA\Property(property: 'precio', type: 'number', example: 349.99),
                    new OA\Property(property: 'stock', type: 'integer', example: 8),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mueble actualizado correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permisos'),
            new OA\Response(response: 404, description: 'Mueble no encontrado'),
        ]
    )]
    public function update(UpdateMuebleRequest $request, Mueble $mueble)
    {
        $mueble->update($request->validated());

        return response()->json([
            'message' => 'Mueble actualizado correctamente',
            'data'    => new MuebleResource($mueble->load('categoria')),
        ]);
    }

    #[OA\Delete(
        path: '/muebles/{id}',
        summary: 'Eliminar un mueble',
        description: 'Requiere token con ability `muebles.eliminar` (rol Administrador o Gestor).',
        tags: ['Muebles'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mueble eliminado correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permisos'),
            new OA\Response(response: 404, description: 'Mueble no encontrado'),
        ]
    )]
    public function destroy(DeleteMuebleRequest $request, Mueble $mueble)
    {
        $mueble->delete();

        return response()->json([
            'message' => 'Mueble eliminado correctamente',
        ]);
    }
}
