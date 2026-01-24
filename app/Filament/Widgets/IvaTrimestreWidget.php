<?php

namespace App\Filament\Widgets;

use App\Models\Factura;
use App\Models\FacturasProveedor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class IvaTrimestreWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    public function getColumnSpan(): int|array
    {
        return 1;
    }

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        [$inicio, $fin] = $this->rangoTrimestreActual();

        $ivaIngresos = (float) Factura::query()
            ->whereIn('estado', ['emitida', 'enviada', 'pagada'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('iva_total');

        $ivaProveedores = (float) FacturasProveedor::query()
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('iva_total');

        return [
            Stat::make('IVA acumulado', $this->formatMoney($ivaIngresos))
                ->color('success'),
            Stat::make('IVA repercutido', $this->formatMoney($ivaProveedores))
                ->color('warning'),
        ];
    }

    private function rangoTrimestreActual(): array
    {
        $inicio = Carbon::now()->startOfQuarter()->startOfDay();
        $fin = Carbon::now()->endOfQuarter()->endOfDay();

        return [$inicio, $fin];
    }

    private function formatMoney(float $value): string
    {
        $signo = $value < 0 ? '-' : '';
        $valor = abs($value);

        return $signo . number_format($valor, 2, ',', '.') . ' €';
    }
}
