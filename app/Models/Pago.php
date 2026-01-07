<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Factura;

class Pago extends Model
{
    protected $fillable = [
        'factura_id',
        'fecha_pago',
        'importe',
        'justificante_path',
    ];

    public function factura()  { 
        return $this->belongsTo(Factura::class); 
    }

    protected static function booted(): void
    {
        static::saved(function (Pago $pago): void {
            if ($pago->wasChanged('factura_id')) {
                $oldFacturaId = $pago->getOriginal('factura_id');
                $oldFactura = $oldFacturaId ? Factura::find($oldFacturaId) : null;

                if ($oldFactura) {
                    static::syncFacturaEstado($oldFactura);
                }
            }

            $factura = $pago->factura()->first();

            if ($factura) {
                static::syncFacturaEstado($factura);
            }
        });

        static::deleted(function (Pago $pago): void {
            $oldFacturaId = $pago->getOriginal('factura_id');
            $factura = $oldFacturaId ? Factura::find($oldFacturaId) : null;

            if ($factura) {
                static::syncFacturaEstado($factura);
            }
        });
    }

    protected static function syncFacturaEstado(Factura $factura): void
    {
        $totalFactura = (float) $factura->total;
        $totalPagado = (float) $factura->pagos()->sum('importe');

        if ($totalFactura > 0 && $totalPagado >= $totalFactura) {
            if ($factura->estado !== 'cobrada') {
                $factura->update(['estado' => 'cobrada']);
            }

            return;
        }

        if ($factura->estado !== 'emitida') {
            $factura->update(['estado' => 'emitida']);
        }
    }
    
}
