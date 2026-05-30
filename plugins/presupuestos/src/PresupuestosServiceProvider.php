<?php

namespace Presupuestos;

use App\Models\Factura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Presupuestos\Models\Presupuesto;

class PresupuestosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/presupuestos.php', 'presupuestos');
    }

    public function boot(): void
    {
        if (!config('plugins.presupuestos', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'presupuestos');

        $this->publishes([
            __DIR__ . '/../config/presupuestos.php' => config_path('presupuestos.php'),
        ], 'presupuestos-config');

        Factura::updated(function (Factura $factura) {
            if (! $factura->wasChanged('estado') || $factura->estado !== 'cobrada') {
                return;
            }

            $presupuestoId = DB::table('presupuestos_facturas')
                ->where('factura_id', $factura->id)
                ->value('presupuesto_id');

            if (! $presupuestoId) {
                return;
            }

            $presupuesto = Presupuesto::find($presupuestoId);

            if (! $presupuesto || $presupuesto->estado !== 'aceptado') {
                return;
            }

            $totalCobrado = Factura::query()
                ->join('presupuestos_facturas', 'facturas.id', '=', 'presupuestos_facturas.factura_id')
                ->where('presupuestos_facturas.presupuesto_id', $presupuestoId)
                ->where('facturas.estado', 'cobrada')
                ->sum('facturas.total');

            if ($totalCobrado >= $presupuesto->total) {
                $presupuesto->forceFill(['estado' => 'facturado'])->save();
            }
        });
    }
}
