<?php

namespace App\Filament\Resources\FacturasProveedores\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Models\FacturasProveedor;
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup, DeleteBulkAction};
use Filament\Support\Icons\Heroicon;
use Carbon\Carbon;

class FacturasProveedoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->description(fn () => view('filament.facturasProveedores.total-stats', [
                'stats' => self::getTotalStats(),
            ]))
            ->columns([
                TextColumn::make('numero_completo')->label('Número')->searchable()->sortable(),
                TextColumn::make('numero_proveedor')->label('Numero Factura')->searchable()->sortable(),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('fecha')->date("d-m-Y")->sortable(),
                TextColumn::make('base')->money('EUR')->sortable(),
                TextColumn::make('total')->money('EUR')->sortable(),
                TextColumn::make('documento')
                    ->label('Doc')
                    ->getStateUsing(fn (FacturasProveedor $record): ?int => $record->pdf_path ? 1 : null)
                    ->formatStateUsing(fn (): string => '')
                    ->icon(fn (FacturasProveedor $record) => $record->pdf_path ? Heroicon::OutlinedArrowDownTray : null)
                    ->url(fn (FacturasProveedor $record): ?string => $record->pdf_path ? route('facturas-proveedores.documento', $record) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->tooltip(fn (FacturasProveedor $record): ?string => $record->pdf_path ? 'Descargar documento' : null)
                    ->alignCenter(),
            ])
            ->filters([
                Filter::make('fecha_rango')
                    ->label('Fecha')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'] ?? null, fn ($q, $date) => $q->whereDate('fecha', '>=', $date))
                            ->when($data['hasta'] ?? null, fn ($q, $date) => $q->whereDate('fecha', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
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

        $total = (float) FacturasProveedor::query()
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');
        $totalMesAnt = (float) FacturasProveedor::query()
            ->whereBetween('fecha', [$inicioMesAnt, $finMesAnt])
            ->sum('total');

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
