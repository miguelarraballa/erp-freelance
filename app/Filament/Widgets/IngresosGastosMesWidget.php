<?php

namespace App\Filament\Widgets;

use App\Models\Factura;
use App\Models\FacturasProveedor;
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
            ->whereIn('estado', ['emitida', 'enviada', 'pagada'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('total');

        $gastos = (float) FacturasProveedor::query()
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('total');

        return [
            Stat::make('Ingresos del mes', $this->formatMoney($ingresos))
                ->color('green'),
            Stat::make('Gastos del mes', $this->formatMoney(-$gastos))
                ->color('red'),
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
