<?php

namespace App\Services;

use App\Models\FacturaProveedor;
use App\Models\Serie;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FacturaEmisionService
{
    /**
     * Emite una factura de prooveedores, asignando serie y número.
     * Idempotente: si ya tiene número, no hace nada.
     */
    public static function emitir(FacturaProveedor $facturaProveedor): FacturaProveedor

    {
        return DB::transaction(function () use ($facturaProveedor) {
            $facturaProveedor->refresh();

            // Si ya tiene número, no repetir
            if (! is_null($facturaProveedor->numero)) {
                return $facturaProveedor;
            }

            $ejercicio = (int) Carbon::parse($facturaProveedor->fecha)->year;

            //Comprobar que existe una serie activa para facturas de proveedor

            // Serie activa del mismo tipo + ejercicio (bloqueo pesimista)
            $serie = Serie::query()
                ->where('tipo', 'proveedor')
                ->where('ejercicio', $ejercicio)
                ->where('activo', true)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$serie) {
                throw new \DomainException('No existe una serie activa para facturas de proveedor en el ejercicio.');
            }

            $seq = (int) $serie->siguiente_numero;

            $numeroCompleto = SerieNumeracionService::renderNumeroCompleto($serie, $seq);

            // Asignar datos en factura
            $facturaProveedor->serie_id        = $serie->id;
            $facturaProveedor->numero          = $seq;
            $facturaProveedor->numero_completo = $numeroCompleto;
            $facturaProveedor->save();

            // Avanzar contador de serie

            $serie->increment('siguiente_numero');

            return $facturaProveedor->fresh();

        });
    }
}
