<?php

namespace App\Filament\Resources\Series\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};

class SeriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prefijo')->searchable()->sortable(),
                TextColumn::make('codigo')->searchable()->sortable(),
                TextColumn::make('sufijo')->searchable()->sortable(),
                TextColumn::make('tipo')->searchable()->sortable(),
                TextColumn::make('ejercicio')->searchable()->sortable(),
                TextColumn::make('siguiente_numero')->searchable()->sortable(),
                IconColumn::make('por_defecto')->boolean(),
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
