<?php

namespace Presupuestos\Services;

use Presupuestos\Models\Presupuesto;
use App\Models\Serie;
use App\Services\SerieNumeracionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PresupuestoEmisionService
{
    /**
     * Emite un presupuesto ya guardado con estado=emitido, asignando serie y número.
     * Idempotente: si ya tiene número, no hace nada.
     */
    public static function emitir(Presupuesto $presupuesto): Presupuesto
    {
        return DB::transaction(function () use ($presupuesto) {
            $presupuesto->refresh();

            if ($presupuesto->estado !== 'emitido') {
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

            // Asignar datos en presupuesto
            $presupuesto->serie_id        = $serie->id;
            $presupuesto->numero          = $seq;
            $presupuesto->numero_completo = $numeroCompleto;
            $presupuesto->ejercicio       = $ejercicio; // si existe el campo
            $presupuesto->save();

            // Avanzar contador de serie
            $serie->increment('siguiente_numero');

            return $presupuesto->fresh();
        });
    }
}
