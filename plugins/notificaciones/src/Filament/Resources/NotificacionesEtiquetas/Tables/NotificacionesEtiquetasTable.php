<?php

namespace Notificaciones\Filament\Resources\NotificacionesEtiquetas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificacionesEtiquetasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tag_name')
                    ->label('Nombre de Etiqueta')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Etiqueta copiada')
                    ->copyMessageDuration(1500),

                TextColumn::make('tag_value')
                    ->label('Valor (Tabla.Campo)')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Valor copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->defaultSort('tag_name', 'asc');
    }
}
