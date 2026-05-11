<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MuebleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio'      => $this->precio,
            'stock'       => $this->stock,
            'color'       => $this->color,
            'material'    => $this->material,
            'imagen_url'  => $this->imagen_url,
            'categoria'   => new CategoriaResource($this->whenLoaded('categoria')),
            'imagenes'    => MuebleImagenResource::collection($this->whenLoaded('imagenes')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
