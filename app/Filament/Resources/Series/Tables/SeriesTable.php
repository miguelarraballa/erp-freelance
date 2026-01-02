<?php

namespace App\Filament\Resources\Series\Tables;

use App\Filament\Resources\Series\SerieResource;
use App\Models\Serie;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\{EditAction, DeleteAction, BulkAction, BulkActionGroup};

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
            ->filters([])
            ->recordActions([
                EditAction::make()
                    // En recursos suele bastar con ->make() a secas. Si prefieres forzar URL:
                    // ->url(fn (Serie $record) => SerieResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn (Serie $record) => $record->facturas()->exists()),
                DeleteAction::make()
                    ->hidden(fn (Serie $record) => $record->facturas()->exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Eliminar seleccionados')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (Serie $serie) {
                                if (! $serie->facturas()->exists()) {
                                    $serie->delete();
                                }
                            });
                        }),
                ]),
            ]);
    }
}