<?php

namespace App\Filament\Widgets;

use App\Models\Factura;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class FacturasPendientesTableWidget extends TableWidget
{
    protected static ?string $heading = 'Facturas pendientes';
    protected static ?int $sort = 2;

    public function getColumnSpan(): int|array
    {
        return 1;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Factura::query()
                    ->whereIn('estado', ['emitida', 'enviada'])
                    ->orderBy('fecha', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_completo')
                    ->label('Número')
                    ->default('Provisional'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('fecha')
                    ->date('Y-m-d'),
                Tables\Columns\TextColumn::make('total')
                    ->money('EUR'),
            ])
            ->defaultSort('fecha', 'asc')
            ->paginated(false);
    }

}
