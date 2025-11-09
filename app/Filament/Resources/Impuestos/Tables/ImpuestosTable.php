<?php

namespace App\Filament\Resources\Impuestos\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use App\Models\Impuestos;

class ImpuestosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('tipo')->searchable()->sortable(),
                TextColumn::make('porcentaje')->searchable()->sortable(),
                TextColumn::make('pais')->searchable()->sortable(),
                IconColumn::make('activo')->boolean(),
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
