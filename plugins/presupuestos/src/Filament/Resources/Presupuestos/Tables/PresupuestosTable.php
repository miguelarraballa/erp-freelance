<?php

namespace Presupuestos\Filament\Resources\Presupuestos\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Presupuestos\Models\Presupuesto;
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup, DeleteBulkAction};
use Filament\Support\Icons\Heroicon;


class PresupuestosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_completo')->label('Número')->searchable()->sortable()->default('Provisional'),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('fecha')->date("Y-m-d")->sortable(),
                TextColumn::make('base')->money('EUR')->sortable(),
                TextColumn::make('total')->money('EUR')->sortable(),
                TextColumn::make('estado')->sortable()->searchable()
                    ->colors([
                    'warning' => 'anulado',
                    'primary' => 'emitido',
                    'secondary' => 'borrador',
                    'success' => 'facturado',
                ]),
                TextColumn::make('pdf')
                    ->label('PDF')
                    ->getStateUsing(fn (Presupuesto $record): ?int => $record->estado !== 'borrador' ? 1 : null)
                    ->formatStateUsing(fn (): string => '')
                    ->icon(fn (Presupuesto $record) => $record->estado !== 'borrador' ? Heroicon::OutlinedArrowDownTray : null)
                    ->url(fn (Presupuesto $record): ?string => $record->estado !== 'borrador' ? route('presupuesto.pdf', $record) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->tooltip(fn (Presupuesto $record): ?string => $record->estado !== 'borrador' ? 'Descargar PDF' : null)
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
