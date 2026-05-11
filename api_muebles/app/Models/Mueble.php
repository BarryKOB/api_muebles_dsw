<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mueble extends Model
{
    use HasFactory;

    protected $table = "muebles";

    // Campos rellenables desde una Request
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'color',
        'material',
        'categoria_id',
        'imagen_url',
    ];

    // Un mueble pertenece a una categoría
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Un mueble puede tener muchas imágenes (galería)
    public function imagenes(): HasMany
    {
        return $this->hasMany(MuebleImagen::class);
    }
}
