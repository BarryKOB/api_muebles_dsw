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
        $query = Mueble::with('categoria');

        // Filtrar por categoría
        if ($request->has('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        // Filtrar por rango de precio
        if ($request->has('precio_min')) {
            $query->where('precio', '>=', $request->precio_min);
        }
        if ($request->has('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }

        // Filtrar por color
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        // Filtrar por material
        if ($request->has('material')) {
            $query->where('material', $request->material);
        }

        // Buscar por texto (nombre o descripción)
        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Ordenar resultados
        if ($request->has('orden')) {
            switch ($request->orden) {
                case 'precio_asc':
                    $query->orderBy('precio', 'asc');
                    break;
                case 'precio_desc':
                    $query->orderBy('precio', 'desc');
                    break;
                case 'nombre_asc':
                    $query->orderBy('nombre', 'asc');
                    break;
                case 'nombre_desc':
                    $query->orderBy('nombre', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        }

        // Paginación (como en lección 1 con paginate)
        $muebles = $query->paginate(10);

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
