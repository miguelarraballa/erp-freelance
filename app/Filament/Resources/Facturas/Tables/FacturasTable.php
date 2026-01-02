<?php

namespace App\Filament\Resources\Facturas\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup, DeleteBulkAction};


class FacturasTable
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
                    'warning' => 'borrador',
                    'primary' => 'emitida',
                    'secondary' => 'enviada',
                    'success' => 'pagada',
                ]),
                IconColumn::make('pagada')->boolean(),
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
