<?php
namespace App\Services;

use App\Models\Factura;

class FacturaCalc
{
    public static function recalcular(Factura $f): Factura
    {
        $base = $iva = $irpf = $total = 0;

        foreach ($f->lineas as $l) {
            $bruto = (float)$l->cantidad * (float)$l->precio_unitario;
            $bruto = $bruto * (1 - ((float)$l->descuento_pct / 100));

            $ivaLinea = 0; $irpfLinea = 0;
            if ($l->impuesto) {
                if ($l->impuesto->tipo === 'iva')  $ivaLinea  = round($bruto * ($l->impuesto->porcentaje/100), 2);
                if ($l->impuesto->tipo === 'irpf') $irpfLinea = round($bruto * ($l->impuesto->porcentaje/100), 2);
            }

            $totalLinea = round($bruto + $ivaLinea - $irpfLinea, 2);

            // Normalizar -0 a 0 para evitar CorruptComponentPayloadException de Livewire
            $baseLinea = round($bruto, 2);
            $baseLinea = $baseLinea == 0 ? 0.0 : $baseLinea;

            $ivaLinea = $ivaLinea == 0 ? 0.0 : $ivaLinea;
            $irpfLinea = $irpfLinea == 0 ? 0.0 : $irpfLinea;
            $totalLinea = $totalLinea == 0 ? 0.0 : $totalLinea;

            $l->base_linea  = $baseLinea;
            $l->iva_linea   = $ivaLinea;
            $l->irpf_linea  = $irpfLinea;
            $l->total_linea = $totalLinea;
            $l->save();

            $base += $l->base_linea;
            $iva  += $l->iva_linea;
            $irpf += $l->irpf_linea;
            $total += $l->total_linea;
        }

        // Normalizar -0 a 0 para evitar CorruptComponentPayloadException de Livewire
        $base = round($base, 2);
        $base = $base == 0 ? 0.0 : $base;

        $iva = round($iva, 2);
        $iva = $iva == 0 ? 0.0 : $iva;

        $irpf = round($irpf, 2);
        $irpf = $irpf == 0 ? 0.0 : $irpf;

        $total = round($total, 2);
        $total = $total == 0 ? 0.0 : $total;

        $f->base = $base;
        $f->iva_total = $iva;
        $f->irpf_total = $irpf;
        $f->total = $total;
        $f->save();

        return $f->refresh();
    }
}