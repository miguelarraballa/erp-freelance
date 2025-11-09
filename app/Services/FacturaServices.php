<?php
namespace App\Services;

use App\Models\Factura;
use App\Models\Serie;
use Illuminate\Support\Facades\DB;

class FacturaService
{
    public static function emitir(Factura $factura): Factura
    {
        return DB::transaction(function () use ($factura) {
            // bloquear la fila de la serie
            $serie = Serie::where('id', $factura->serie_id)->lockForUpdate()->firstOrFail();

            $numero = $serie->siguiente_numero;
            $serie->siguiente_numero = $numero + 1;
            $serie->save();

            $pref = $serie->prefijo ?? '';
            $suf  = $serie->sufijo ?? '';
            $numeroCompleto = $pref . $numero . $suf;

            $factura->numero = $numero;
            $factura->numero_completo = $numeroCompleto;
            $factura->estado = 'emitida';
            if (!$factura->fecha) {
                $factura->fecha = now()->toDateString();
            }
            $factura->save();

            return $factura->refresh();
        });
    }
}