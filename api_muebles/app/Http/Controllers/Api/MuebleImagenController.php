<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MuebleImagen;
use App\Models\Mueble;
use Illuminate\Http\Request;

class MuebleImagenController extends Controller
{
    // Obtener todas las imágenes de un mueble
    public function index($mueble_id)
    {
        $mueble = Mueble::find($mueble_id);
        
        if (!$mueble) {
            return response()->json(['mensaje' => 'Mueble no encontrado'], 404);
        }

        $imagenes = $mueble->imagenes()->orderBy('orden')->get();
        
        return response()->json([
            'exito' => true,
            'datos' => $imagenes
        ], 200);
    }

    // Guardar una nueva imagen (subida de archivo)
    public function store(Request $request)
    {
        // Validar que venga el archivo y que sea válido
        $request->validate([
            'mueble_id' => 'required|exists:muebles,id',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'orden' => 'integer|min:1'
        ]);

        // Verificar que el mueble existe
        $mueble = Mueble::find($request->mueble_id);
        if (!$mueble) {
            return response()->json(['mensaje' => 'Mueble no encontrado'], 404);
        }

        // Guardar el archivo en storage/app/public/muebles
        $archivo = $request->file('imagen');
        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $ruta = $archivo->storeAs('muebles', $nombreArchivo, 'public');

        // Construir la URL pública del archivo
        $url = '/storage/' . $ruta;

        // Guardar en la BD
        $imagen = MuebleImagen::create([
            'mueble_id' => $request->mueble_id,
            'url' => $url,
            'orden' => $request->orden ?? 1
        ]);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Imagen subida correctamente',
            'datos' => $imagen
        ], 201);
    }

    // Obtener una imagen específica
    public function show($id)
    {
        $imagen = MuebleImagen::find($id);
        
        if (!$imagen) {
            return response()->json(['mensaje' => 'Imagen no encontrada'], 404);
        }

        return response()->json([
            'exito' => true,
            'datos' => $imagen
        ], 200);
    }

    // Actualizar una imagen
    public function update(Request $request, $id)
    {
        $imagen = MuebleImagen::find($id);
        
        if (!$imagen) {
            return response()->json(['mensaje' => 'Imagen no encontrada'], 404);
        }

        $request->validate([
            'imagen' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'orden' => 'integer|min:1'
        ]);

        // Si viene una nueva imagen, la guardamos
        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior (opcional)
            // Storage::disk('public')->delete(str_replace('/storage/', '', $imagen->url));

            $archivo = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $ruta = $archivo->storeAs('muebles', $nombreArchivo, 'public');
            $imagen->url = '/storage/' . $ruta;
        }

        // Actualizar orden si viene en la request
        if ($request->has('orden')) {
            $imagen->orden = $request->orden;
        }

        $imagen->save();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Imagen actualizada correctamente',
            'datos' => $imagen
        ], 200);
    }

    // Eliminar una imagen
    public function destroy($id)
    {
        $imagen = MuebleImagen::find($id);
        
        if (!$imagen) {
            return response()->json(['mensaje' => 'Imagen no encontrada'], 404);
        }

        // Eliminar el archivo del disco
        // $rutaArchivo = str_replace('/storage/', '', $imagen->url);
        // Storage::disk('public')->delete($rutaArchivo);

        $imagen->delete();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Imagen eliminada correctamente'
        ], 200);
    }
}
