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

class MuebleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Público: no requiere token.
     * Soporta filtros, búsqueda, ordenación y paginación.
     */
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

    /**
     * Display the specified resource.
     * Público: no requiere token.
     */
    public function show(Mueble $mueble)
    {
        // Cargamos la categoría y las imágenes (como en lección 1 con loadMissing)
        return new MuebleResource($mueble->loadMissing(['categoria', 'imagenes']));
    }

    /**
     * Store a newly created resource in storage.
     * Protegido: requiere token con ability 'muebles.crear'
     */
    public function store(StoreMuebleRequest $request)
    {
        $mueble = Mueble::create($request->validated());

        return response()->json([
            'message' => 'Mueble creado correctamente',
            'data'    => new MuebleResource($mueble->load('categoria')),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     * Protegido: requiere token con ability 'muebles.editar'
     */
    public function update(UpdateMuebleRequest $request, Mueble $mueble)
    {
        $mueble->update($request->validated());

        return response()->json([
            'message' => 'Mueble actualizado correctamente',
            'data'    => new MuebleResource($mueble->load('categoria')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Protegido: requiere token con ability 'muebles.eliminar'
     */
    public function destroy(DeleteMuebleRequest $request, Mueble $mueble)
    {
        $mueble->delete();

        return response()->json([
            'message' => 'Mueble eliminado correctamente',
        ]);
    }
}
