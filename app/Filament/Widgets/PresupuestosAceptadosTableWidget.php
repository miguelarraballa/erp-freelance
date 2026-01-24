<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Presupuestos\Models\Presupuesto;

class PresupuestosAceptadosTableWidget extends TableWidget
{
    protected static ?string $heading = 'Presupuestos aceptados';
    protected static ?int $sort = 5;

    public function getColumnSpan(): int|array
    {
        return 1;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Presupuesto::query()
                    ->where('estado', 'aceptado')
                    ->orderByDesc('updated_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_completo')
                    ->label('Numero')
                    ->default('Provisional'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aprobado')
                    ->date('Y-m-d'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated(false);
    }
}
