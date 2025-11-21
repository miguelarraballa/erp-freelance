<?php

namespace App\Filament\Resources\Clientes\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use App\Models\Cliente;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mostrar')->searchable()->sortable(),
                TextColumn::make('nif')->label('NIF/CIF/NIE')->searchable()->sortable(),
                TextColumn::make('ciudad'),
                IconColumn::make('cliente')->boolean(),
                IconColumn::make('proveedor')->boolean(),
                IconColumn::make('activo')->boolean(),
            ])
            ->filters([])
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
