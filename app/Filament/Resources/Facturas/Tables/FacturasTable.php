<?php

namespace App\Filament\Resources\Facturas\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Factura;
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup, DeleteBulkAction};
use Filament\Support\Icons\Heroicon;


class FacturasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('numero_completo')->label('Número')->searchable()->sortable()->default('Provisional'),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('fecha')->date("Y-m-d")->sortable(),
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
                //
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
}
