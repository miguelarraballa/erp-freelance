<?php

namespace Presupuestos\Services;

use Presupuestos\Models\Presupeustos;
use App\Models\Serie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PresupuestoEmisionService
{
    /**
     * Emite una factura ya guardada con estado=emitida, asignando serie y número.
     * Idempotente: si ya tiene número, no hace nada.
     */
    public static function emitir(Presupuestos $presupuesto): Presupuestos
    {
        return DB::transaction(function () use ($presupuesto) {
            $presupuesto->refresh();

            if ($presupuesto->estado !== 'emitida') {
                throw new \DomainException('El presupuesto no está en estado "emitido".');
            }

            // Si ya tiene número, no repetir
            if (! is_null($presupuesto->numero)) {
                return $presupuesto;
            }

            $ejercicio = (int) Carbon::parse($presupuesto->fecha)->year;
            // Serie activa del mismo tipo + ejercicio (bloqueo pesimista)
            $serie = Serie::query()
                ->where('tipo', $presupuesto->tipo)
                ->where('ejercicio', $ejercicio)
                ->where('activo', true)
                ->lockForUpdate()
                ->firstOrFail();

            $seq = (int) $serie->siguiente_numero;

            $numeroCompleto = SerieNumeracionService::renderNumeroCompleto($serie, $seq);

            // Asignar datos en factura
            $factura->serie_id        = $serie->id;
            $factura->numero          = $seq;
            $factura->numero_completo = $numeroCompleto;
            $factura->ejercicio       = $ejercicio; // si existe el campo
            $factura->save();

            // Avanzar contador de serie
            $serie->increment('siguiente_numero');

            return $factura->fresh();
        });
    }
}
