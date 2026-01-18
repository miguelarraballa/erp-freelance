<?php
namespace Presupuestos\Services;

use Presupuestos\Models\Presupuesto;

class PresupuestoCalc
{
    public static function recalcular(Presupuesto $f): Presupuesto
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

            $l->base_linea  = round($bruto, 2);
            $l->iva_linea   = $ivaLinea;
            $l->irpf_linea  = $irpfLinea;
            $l->total_linea = $totalLinea;
            $l->save();

            $base += $l->base_linea;
            $iva  += $l->iva_linea;
            $irpf += $l->irpf_linea;
            $total += $l->total_linea;
        }

        $f->base = round($base, 2);
        $f->iva_total = round($iva, 2);
        $f->irpf_total = round($irpf, 2);
        $f->total = round($total, 2);
        $f->save();

        return $f->refresh();
    }
}
