<?php
namespace Presupuestos\Services;

use Presupeustos\Models\Presupeustos;
use App\Models\Serie;
use Illuminate\Support\Facades\DB;

class PresupuestoService
{
    public static function emitir(Presupuesto $presupuesto): Presupuesto
    {
        return DB::transaction(function () use ($presupuesto) {
            $presupuesto->refresh();

            if (!is_null($presupuesto->numero)) {
                return $presupuesto;
            }

            if (empty($presupuesto->serie_id)) {
                $ejercicio = (int) Carbon::parse($presupuesto->fecha ?? now()->toDateString())->year;

                $serie = Serie::query()
                    ->where('tipo', $presupuesto->tipo)
                    ->where('ejercicio', $ejercicio)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $presupuesto->serie_id = $serie->id;
                $presupuesto->save();
            }

            $serie = Serie::whereKey($presupuesto->serie_id)->lockForUpdate()->firstOrFail();

            $seq = (int) $serie->siguiente_numero;

            $pattern = (string) ($serie->codigo ?? 'NNNN');
            $codigoRender = preg_replace_callback('/N+/', function ($m) use ($seq) {
                $len = strlen($m[0]);
                return str_pad((string) $seq, $len, '0', STR_PAD_LEFT);
            }, $pattern);

            $suf = (string) ($serie->sufijo ?? '');
            $pre = (string) ($serie->prefijo ?? '');
            $numeroCompleto = $pre . $codigoRender . $suf;

            $presupuesto->numero = $seq;
            $presupuesto->numero_completo = $numeroCompleto;
            $presupuesto->estado = 'emitida';
            if (empty($presupuesto->fecha)) {
                $presupuesto->fecha = now()->toDateString();
            }
            $presupuesto->save();

            $serie->increment('siguiente_numero');

            return $presupuesto->fresh();
        });
    }
}