<?php

namespace App\Filament\Resources\Facturas\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Models\Factura;
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup, DeleteBulkAction};
use Filament\Support\Icons\Heroicon;
use Carbon\Carbon;


class FacturasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->description(fn () => view('filament.facturas.total-stats', [
                'stats' => self::getTotalStats()
            ]))
            ->columns([
                TextColumn::make('numero_completo')->label('Número')->searchable()->sortable()->default('Provisional'),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('fecha')->date("d-m-Y")->sortable(),
                TextColumn::make('base')->money('EUR')->sortable(),
                TextColumn::make('total')->money('EUR')->sortable(),
                TextColumn::make('estado')->sortable()->searchable()
                    ->colors([
                    'warning' => 'borrador',
                    'primary' => 'emitida',
                    'secondary' => 'enviada',
                    'success' => 'pagada',
                ]),
                TextColumn::make('pagada')
                    ->label('Pagada')
                    ->getStateUsing(fn (Factura $record): float => (float) $record->pagos()->sum('importe'))
                    ->money('EUR')
                    ->color(function (Factura $record): string {
                        $totalFactura = (float) $record->total;
                        $totalPagado = (float) $record->pagos()->sum('importe');

                        if ($totalPagado === $totalFactura) {
                            return 'success';
                        }

                        if ($totalPagado < $totalFactura) {
                            return 'danger';
                        }

                        return 'warning';
                    }),
                TextColumn::make('pdf')
                    ->label('PDF')
                    ->getStateUsing(fn (Factura $record): ?int => $record->estado !== 'borrador' ? 1 : null)
                    ->formatStateUsing(fn (): string => '')
                    ->icon(fn (Factura $record) => $record->estado !== 'borrador' ? Heroicon::OutlinedArrowDownTray : null)
                    ->url(fn (Factura $record): ?string => $record->estado !== 'borrador' ? route('facturas.pdf', $record) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->tooltip(fn (Factura $record): ?string => $record->estado !== 'borrador' ? 'Descargar PDF' : null)
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

        $total = (float) Factura::query()
            ->where('estado', '=', 'cobrada')
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');
        
        
        $totalMesAnt = (float) Factura::query()
            ->where('estado', '=', 'cobrada')
            ->whereBetween('fecha', [$inicioMesAnt, $finMesAnt])
            ->sum('total');
        

        $estimado = (float) Factura::query()
            ->where('estado', '=', ['cobrada', 'emitida'])
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total');
            
        return [
            'Total' => $total,
            'TotalMesAnterior' => $totalMesAnt,
            'Estimada' => $estimado,
        ];
    }

    protected static function getTotalStats(): array
    {
        $totales = self::getTotalesAcumulados();
        $total = number_format($totales['Total'], 2, ',', '.');
        $totalMesAnterior = number_format($totales['TotalMesAnterior'], 2, ',', '.');
        $estimado = number_format($totales['Estimada'], 2, ',', '.');

        return [
            [
                'label' => 'Total emitido en el mes actual',
                'value' => $estimado . ' €',
            ],
            [
                'label' => 'Total cobrado en el mes actual',
                'value' => $total . ' €',
            ],
            [
                'label' => 'Total cobrado en el mes anterior',
                'value' => $totalMesAnterior . ' €',
            ],

        ];
    }
}
