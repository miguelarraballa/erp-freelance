<?php

namespace App\Filament\Widgets;

use App\Models\Factura;
use App\Models\FacturasProveedor;
use gastos\Models\Gasto;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class IngresosGastosMesWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public function getColumnSpan(): int|array
    {
        return 2;
    }

    public function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        [$inicio, $fin] = $this->rangoMesActual();

        $ingresos = (float) Factura::query()
            ->whereIn('estado', ['cobrada'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('total');

        $gastos = (float) FacturasProveedor::query()
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('total');

        $gastos_nf = (float) Gasto::query()
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('importe');
        

        return [
            Stat::make('Ingresos del mes', $this->formatMoney($ingresos))
                ->color('success'),
            Stat::make('Gastos de proveedores', $this->formatMoney(-$gastos))
                ->color('danger'),
            Stat::make('Gastos no facturables', $this->formatMoney(-$gastos_nf))
                ->color('warning'),
            Stat::make('Balance', $this->formatMoney($ingresos - $gastos - $gastos_nf))
                ->color('primary'),
        ];
    }

    private function rangoMesActual(): array
    {
        $inicio = Carbon::now()->startOfMonth()->startOfDay();
        $fin = Carbon::now()->endOfMonth()->endOfDay();

        return [$inicio, $fin];
    }

    private function formatMoney(float $value): string
    {
        $signo = $value < 0 ? '-' : '';
        $valor = abs($value);

        return $signo . number_format($valor, 2, ',', '.') . ' €';
    }

}
