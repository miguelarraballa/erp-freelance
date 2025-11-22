<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $fillable = ['codigo','prefijo','sufijo','siguiente_numero','por_defecto','activo','tipo','ejercicio'];

    public function facturas() { return $this->hasMany(Factura::class); }

    protected static function booted(): void
    {
        static::saving(function (self $serie) {
            // si se activa, desactiva las demás del mismo tipo+ejercicio
            if ($serie->activo) {
                static::where('tipo', $serie->tipo)
                    ->where('ejercicio', $serie->ejercicio)
                    ->whereKeyNot($serie->id)
                    ->update(['activo' => false]);
            }
        });
    }
}