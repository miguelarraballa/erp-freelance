<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = [
        'serie_id','numero','numero_completo','cliente_id','fecha','vencimiento','estado','tipo',
        'verificacion_hash','base','iva_total','irpf_total','total','moneda','notas',
        'rectifica_id','created_by','updated_by','datos_facturacion',
    ];

    public function serie()   { 
        return $this->belongsTo(Serie::class); 
    }

    public function cliente() { 
        return $this->belongsTo(Cliente::class); 
    }
    
    public function lineas()  { 
        return $this->hasMany(FacturaLinea::class)->orderBy('orden'); 
    }

    public function pagos()  { 
        return $this->hasMany(Pago::class)->orderBy('fecha_pago'); 
    }
    
    public function logs()    { 
        return $this->hasMany(FacturaLog::class)->latest('id'); 
    }

    public function rectifica()
    {
        return $this->belongsTo(self::class, 'rectifica_id');
    }

    public function rectificadas()
    {
        return $this->hasMany(self::class, 'rectifica_id');
    }

    protected static function booted(): void
    {
        static::updating(function (Factura $f) {
            $origEstado = $f->getOriginal('estado');
            $origNumero = $f->getOriginal('numero');

            // Si aún es borrador, no imponemos restricciones aquí.
            if ($origEstado === 'borrador') {
                return;
            }

            // Caso 1: transición de emisión (ya está en 'emitida' pero AÚN sin número):
            // permitimos fijar numeración y serie/fecha en esta pasada.
            if ($origEstado === 'emitida' && is_null($origNumero)) {
                $permitidos = [
                    'estado',
                    'serie_id',
                    'numero',
                    'numero_completo',
                    'fecha',
                    $f->getUpdatedAtColumn(),
                ];

                foreach (array_keys($f->getDirty()) as $campo) {
                    if (! in_array($campo, $permitidos, true)) {
                        // revertimos silenciosamente cualquier otro cambio
                        $f->{$campo} = $f->getOriginal($campo);
                    }
                }

                return;
            }

            // Caso 2: ya emitida Y numerada → solo se puede cambiar 'estado'
            if ($origEstado === 'emitida' && ! is_null($origNumero)) {
                $permitidos = ['estado', $f->getUpdatedAtColumn()];

                foreach (array_keys($f->getDirty()) as $campo) {
                    if (! in_array($campo, $permitidos, true)) {
                        // revertimos silenciosamente cualquier otro cambio
                        $f->{$campo} = $f->getOriginal($campo);
                    }
                }
            }
        });

        static::deleting(function (Factura $f) {
            if ($f->estado !== 'borrador') {
                throw new \DomainException('Solo se puede borrar una factura en estado borrador.');
            }
        });
    }

}
