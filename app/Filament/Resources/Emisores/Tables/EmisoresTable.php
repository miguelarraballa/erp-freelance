<?php

namespace App\Filament\Resources\Emisores\Tables;

use App\Filament\Resources\Emisores\EmisorResource;
use App\Models\Emisor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup, DeleteBulkAction};
use Filament\Actions\Action;                       



class EmisoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('nif')->searchable(),
                TextColumn::make('ciudad')->searchable(),
                TextColumn::make('provincia')->searchable(),
                TextColumn::make('pais')->label('País'),
                IconColumn::make('activo')->boolean(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Emisor $record) => EmisorResource::getUrl('edit', ['record' => $record])),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}