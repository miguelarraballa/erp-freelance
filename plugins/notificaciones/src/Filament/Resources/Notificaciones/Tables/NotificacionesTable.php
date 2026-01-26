<?php

namespace Notificaciones\Filament\Resources\Notificaciones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Notificaciones\Enums\NotificacionEstado;

class NotificacionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('plantilla.nombre')->label('Plantilla')->searchable()->sortable(),
                TextColumn::make('fecha')->dateTime()->sortable(),
                TextColumn::make('estado')
                    ->formatStateUsing(function (?string $state): ?string {
                        if ($state === null) {
                            return null;
                        }

                        return NotificacionEstado::tryFrom($state)?->label() ?? $state;
                    })
                    ->sortable(),
                TextColumn::make('fecha_envio')->dateTime()->sortable(),
                TextColumn::make('relacionado_tabla')->label('Tabla')->sortable(),
                TextColumn::make('relacionado_id')->label('ID')->sortable(),
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
