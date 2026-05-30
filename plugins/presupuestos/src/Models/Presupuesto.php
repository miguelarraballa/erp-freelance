<?php

namespace Presupuestos\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Models\Serie;
use Illuminate\Support\Facades\DB;


class Presupuesto extends Model
{
    protected $fillable = [
        'serie_id','numero','numero_completo','cliente_id','fecha','vencimiento','estado',
        'base','iva_total','irpf_total','total','moneda','notas',
        'created_by','updated_by','datos_facturacion',
    ];

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function lineas()
    {
        return $this->hasMany(PresupuestoLinea::class)->orderBy('orden');
    }

    public function facturas()
    {
        return $this->belongsToMany(\App\Models\Factura::class, 'presupuestos_facturas');
    }

    public function tieneFacturaAsociada(): bool
    {
        return $this->exists && DB::table('presupuestos_facturas')
            ->where('presupuesto_id', $this->id)
            ->exists();
    }

    public function estaFacturado(): bool
    {
        return $this->estado === 'facturado' || $this->tieneFacturaAsociada();
    }

    protected static function booted(): void
    {
        static::updating(function (Presupuesto $presupuesto) {
            $estadoOriginal = $presupuesto->getOriginal('estado');
            $tieneFactura = $presupuesto->tieneFacturaAsociada();

            if ($estadoOriginal === 'facturado' && $tieneFactura) {
                foreach (array_keys($presupuesto->getDirty()) as $campo) {
                    if ($campo !== $presupuesto->getUpdatedAtColumn()) {
                        $presupuesto->{$campo} = $presupuesto->getOriginal($campo);
                    }
                }

                return;
            }

            if ($estadoOriginal === 'borrador') {
                return;
            }

            if ($estadoOriginal === 'facturado'
                && $presupuesto->isDirty('estado')
                && $presupuesto->estado !== 'emitido'
            ) {
                $presupuesto->estado = $estadoOriginal;
            }

            if ($estadoOriginal === 'aceptado'
                && $presupuesto->isDirty('estado')
                && ! in_array($presupuesto->estado, ['emitido', 'aceptado', 'facturado'], true)
            ) {
                $presupuesto->estado = $estadoOriginal;
            }

            foreach (array_keys($presupuesto->getDirty()) as $campo) {
                if (! in_array($campo, ['estado', $presupuesto->getUpdatedAtColumn()], true)) {
                    $presupuesto->{$campo} = $presupuesto->getOriginal($campo);
                }
            }
        });

        static::deleting(function (Presupuesto $presupuesto) {
            if ($presupuesto->estado !== 'borrador') {
                throw new \DomainException('Solo se puede borrar un presupuesto en estado borrador.');
            }
        });
    }

}
