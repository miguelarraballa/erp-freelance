<?php

namespace Proyectos\Filament\Resources\ProyectoTareas\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class ProyectoTareasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('proyecto.nombre')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('inicio')
                    ->label('Inicio')
                    ->formatStateUsing(fn ($state) => $state ? substr((string) $state, 0, 5) : null),
                TextColumn::make('fin')
                    ->label('Fin')
                    ->formatStateUsing(fn ($state) => $state ? substr((string) $state, 0, 5) : null),
                TextColumn::make('duracion')
                    ->label('Duración')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                IconColumn::make('facturado')
                    ->label('Facturado')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\DeleteAction::make()
                    ->disabled(fn ($record) => (bool) $record->facturado)
                    ->hidden(fn ($record) => (bool) $record->facturado),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->disabled(fn ($records) => $records->where('facturado', true)->count() > 0),
                ]),
            ]);
    }
}
