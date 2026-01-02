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
            $factura->refresh();

            if (!is_null($factura->numero)) {
                return $factura;
            }

            if (empty($factura->serie_id)) {
                $ejercicio = (int) Carbon::parse($factura->fecha ?? now()->toDateString())->year;

                $serie = Serie::query()
                    ->where('tipo', $factura->tipo)
                    ->where('ejercicio', $ejercicio)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $factura->serie_id = $serie->id;
                $factura->save();
            }

            $serie = Serie::whereKey($factura->serie_id)->lockForUpdate()->firstOrFail();

            $seq = (int) $serie->siguiente_numero;

            $pattern = (string) ($serie->codigo ?? 'NNNN');
            $codigoRender = preg_replace_callback('/N+/', function ($m) use ($seq) {
                $len = strlen($m[0]);
                return str_pad((string) $seq, $len, '0', STR_PAD_LEFT);
            }, $pattern);

            $suf = (string) ($serie->sufijo ?? '');
            $pre = (string) ($serie->prefijo ?? '');
            $numeroCompleto = $pre . $codigoRender . $suf;

            $factura->numero = $seq;
            $factura->numero_completo = $numeroCompleto;
            $factura->estado = 'emitida';
            if (empty($factura->fecha)) {
                $factura->fecha = now()->toDateString();
            }
            $factura->save();

            $serie->increment('siguiente_numero');

            return $factura->fresh();
        });
    }
}