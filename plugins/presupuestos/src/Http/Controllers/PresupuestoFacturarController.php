<?php

namespace Presupuestos\Http\Controllers;

use App\Filament\Resources\Facturas\FacturaResource;
use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Services\FacturaCalc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Presupuestos\Models\Presupuesto;

class PresupuestoFacturarController extends Controller
{
    public function create(Request $request, Presupuesto $presupuesto): RedirectResponse
    {
        if ($presupuesto->estado !== 'aceptado') {
            abort(403, 'El presupuesto no está en estado aceptado.');
        }

        $presupuesto->load(['lineas']);

        return DB::transaction(function () use ($presupuesto) {
            $userId = auth()->id();

            $factura = Factura::create([
                'cliente_id' => $presupuesto->cliente_id,
                'datos_facturacion' => $presupuesto->datos_facturacion,
                'fecha' => $presupuesto->fecha ?? now()->toDateString(),
                'vencimiento' => $presupuesto->vencimiento ?? now()->addWeek()->toDateString(),
                'estado' => 'borrador',
                'tipo' => 'normal',
                'moneda' => $presupuesto->moneda ?? 'eur',
                'notas' => $presupuesto->notas,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($presupuesto->lineas as $linea) {
                FacturaLinea::create([
                    'factura_id' => $factura->id,
                    'orden' => $linea->orden,
                    'producto' => $linea->producto,
                    'concepto' => $linea->concepto,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'descuento_pct' => $linea->descuento_pct,
                    'impuesto_id' => $linea->impuesto_id,
                    'base_linea' => $linea->base_linea,
                    'iva_linea' => $linea->iva_linea,
                    'irpf_linea' => $linea->irpf_linea,
                    'total_linea' => $linea->total_linea,
                ]);
            }

            FacturaCalc::recalcular($factura->fresh('lineas.impuesto'));

            DB::table('presupuestos_facturas')->updateOrInsert(
                [
                    'presupuesto_id' => $presupuesto->id,
                    'factura_id' => $factura->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return redirect(FacturaResource::getUrl('edit', ['record' => $factura]));
        });
    }
}
