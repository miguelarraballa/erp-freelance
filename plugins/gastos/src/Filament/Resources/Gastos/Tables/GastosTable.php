<?php

namespace Gastos\Filament\Resources\Gastos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Gastos\Models\Gasto;
use Carbon\Carbon;

class GastosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->description(fn () => view('gastos::filament.total-stats', [
                'stats' => self::getTotalStats(),
            ]))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categoria')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('importe')
                    ->label('Importe')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

        protected static function getTotalesAcumulados(): array
    {
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();
        $inicioMesAnt = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $finMesAnt = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $total = (float) Gasto::query()
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('importe');
        $totalMesAnt = (float) Gasto::query()
            ->whereBetween('fecha', [$inicioMesAnt, $finMesAnt])
            ->sum('importe');

        return [
            'Total' => $total,
            'TotalMesAnterior' => $totalMesAnt,
        ];
    }

    protected static function getTotalStats(): array
    {
        $totales = self::getTotalesAcumulados();
        $total = number_format($totales['Total'], 2, ',', '.');
        $totalMesAnterior = number_format($totales['TotalMesAnterior'], 2, ',', '.');

        return [
            [
                'label' => 'Total pagado en el mes actual',
                'value' => $total . ' €',
            ],
            [
                'label' => 'Total pagado en el mes anterior',
                'value' => $totalMesAnterior . ' €',
            ],
        ];
    }
}
