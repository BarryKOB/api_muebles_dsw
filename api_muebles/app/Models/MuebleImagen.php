<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MuebleImagen extends Model
{
    use HasFactory;

    protected $table = "mueble_imagenes";

    protected $fillable = [
        'mueble_id',
        'url',
        'orden',
    ];

    // Una imagen pertenece a un mueble
    public function mueble(): BelongsTo
    {
        return $this->belongsTo(Mueble::class);
    }
}
