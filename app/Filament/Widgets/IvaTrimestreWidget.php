<?php

namespace App\Filament\Widgets;

use App\Models\Factura;
use App\Models\FacturasProveedor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Carbon;

class IvaTrimestreWidget extends TableWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'IVA trimestre';

    public function getColumnSpan(): int|array
    {
        return 1;
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => $this->get_collection_iva())
            ->columns([
                Tables\Columns\TextColumn::make('iva_acumulado')
                    ->label('IVA acumulado')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('iva_repercutido')
                    ->label('IVA repercutido')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('iva_total')
                    ->label('IVA total')
                    ->money('EUR'),
            ])
            ->paginated(false);
    }

    private function rangoTrimestreActual(): array
    {
        $inicio = Carbon::now()->startOfQuarter()->startOfDay();
        $fin = Carbon::now()->endOfQuarter()->endOfDay();

        return [$inicio, $fin];
    }

    protected function get_collection_iva()  {

        [$inicio, $fin] = $this->rangoTrimestreActual();

        $ivaIngresos = (float) Factura::query()
            ->whereIn('estado', ['emitida', 'cobrada'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('iva_total');

        $ivaProveedores = (float) FacturasProveedor::query()
            ->whereBetween('fecha', [$inicio, $fin])
            ->sum('iva_total');

        $ivaTotal = $ivaIngresos - $ivaProveedores;

        return collect([
            [
                'iva_acumulado' => $ivaIngresos,
                'iva_repercutido' => $ivaProveedores,
                'iva_total' => $ivaTotal,
            ],
        ]);
    }
    
}
