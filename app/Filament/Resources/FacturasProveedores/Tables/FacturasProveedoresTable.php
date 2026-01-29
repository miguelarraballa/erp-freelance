<?php

namespace App\Filament\Resources\FacturasProveedores\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\FacturasProveedor;
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup, DeleteBulkAction};
use Filament\Support\Icons\Heroicon;

class FacturasProveedoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('numero_completo')->label('Número')->searchable()->sortable(),
                TextColumn::make('numero_proveedor')->label('Numero Factura')->searchable()->sortable(),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('fecha')->date("Y-m-d")->sortable(),
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
