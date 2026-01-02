<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Serie;
use App\Models\FacturaLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FacturaEmisionService
{
    /**
     * Emite una factura ya guardada con estado=emitida, asignando serie y número.
     * Idempotente: si ya tiene número, no hace nada.
     */
    public static function emitir(Factura $factura): Factura
    {
        return DB::transaction(function () use ($factura) {
            $factura->refresh();

            if ($factura->estado !== 'emitida') {
                throw new \DomainException('La factura no está en estado "emitida".');
            }

            // Si ya tiene número, no repetir
            if (! is_null($factura->numero)) {
                return $factura;
            }

            $ejercicio = (int) Carbon::parse($factura->fecha)->year;

            // Serie activa del mismo tipo + ejercicio (bloqueo pesimista)
            $serie = Serie::query()
                ->where('tipo', $factura->tipo)
                ->where('ejercicio', $ejercicio)
                ->where('activo', true)
                ->lockForUpdate()
                ->firstOrFail();

            $seq = (int) $serie->siguiente_numero;

            // Renderizar el patrón de la serie: sustituir grupos de N por el secuencial con ceros
            $pattern = (string) $serie->codigo; // p.ej. "NNNN", "FAC-NNN/{$ejercicio}"
            $codigoRender = preg_replace_callback('/N+/', function ($m) use ($seq) {
                $len = strlen($m[0]);
                return str_pad((string) $seq, $len, '0', STR_PAD_LEFT);
            }, $pattern);

            $numeroCompleto = (string) (($serie->prefijo ?? '') . $codigoRender . ($serie->sufijo ?? ''));

            // Asignar datos en factura
            $factura->serie_id        = $serie->id;
            $factura->numero          = $seq;
            $factura->numero_completo = $numeroCompleto;
            $factura->ejercicio       = $ejercicio; // si existe el campo
            $factura->save();

            // Avanzar contador de serie
            $serie->increment('siguiente_numero');

            // Log inmutable
            FacturaLog::create([
                'factura_id' => $factura->id,
                'accion'     => 'emitida',
                'usuario_id' => Auth::id(),
                'payload'    => [
                    'serie_id'        => $serie->id,
                    'numero'          => $seq,
                    'numero_completo' => $numeroCompleto,
                    'totales'         => [
                        'base'       => $factura->base,
                        'iva_total'  => $factura->iva_total,
                        'irpf_total' => $factura->irpf_total,
                        'total'      => $factura->total,
                    ],
                ],
            ]);

            return $factura->fresh();
        });
    }
}