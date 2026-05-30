<?php

namespace Servidores\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Servidores\Models\Servidor;

class ServidoresRenovacionWidget extends TableWidget
{
    protected static ?string $heading = 'Servidores próximos a renovar';
    protected static ?int $sort = 10;

    public function getColumnSpan(): int|array
    {
        return 2;
    }

    public function table(Table $table): Table
    {
        $hoy = Carbon::today();
        $limiteAnterior = $hoy->copy()->startOfMonth();
        $limiteSuperior = $hoy->copy()->addMonth()->startOfMonth()->addDays(14);

        return $table
            ->query(
                Servidor::query()
                    ->where('activo', true)
                    ->where('fecha_renovacion', '<=', $limiteSuperior)
                    ->orderBy('fecha_renovacion', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Servidor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente'),

                Tables\Columns\TextColumn::make('fecha_renovacion')
                    ->label('Renovación')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(fn (Servidor $record): string => match (true) {
                        $record->fecha_renovacion->lt($limiteAnterior) => 'danger',
                        $record->fecha_renovacion->month === $hoy->month
                            && $record->fecha_renovacion->year === $hoy->year => 'warning',
                        default => 'info',
                    }),
            ])
            ->paginated(false);
    }
}
