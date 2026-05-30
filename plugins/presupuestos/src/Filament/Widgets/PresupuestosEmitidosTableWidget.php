<?php

namespace Presupuestos\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Presupuestos\Models\Presupuesto;

class PresupuestosEmitidosTableWidget extends TableWidget
{
    protected static ?string $heading = 'Presupuestos pendientes';
    protected static ?int $sort = 4;

    public function getColumnSpan(): int|array
    {
        return 1;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Presupuesto::query()
                    ->where('estado', 'emitido')
                    ->orderBy('fecha', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_completo')
                    ->label('Numero')
                    ->default('Provisional'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('Y-m-d'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR'),
            ])
            ->defaultSort('fecha', 'asc')
            ->paginated(false);
    }
}
