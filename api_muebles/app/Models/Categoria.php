<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = "categorias";

    // Campos rellenables desde una Request (mismo patrón que Cliente en lección 1)
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Una categoría puede tener muchos muebles
    public function muebles(): HasMany
    {
        return $this->hasMany(Mueble::class);
    }
}
