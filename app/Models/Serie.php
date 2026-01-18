<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Presupuestos\Models\Presupuesto;



class Serie extends Model
{
    protected $fillable = ['codigo','prefijo','sufijo','siguiente_numero','por_defecto','activo','tipo','ejercicio'];

    public function facturas() 
    { 
        return $this->hasMany(Factura::class); 
    }

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class);
    }

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

        static::deleting(function (self $serie) {
            if ($serie->facturas()->exists()) {
                throw new \DomainException('No se puede borrar: la serie ya está en uso por facturas.');
            }
        });

        static::updating(function (self $serie) {
            
        });
    }

 
}
