<?php

namespace Notificaciones\Filament\Resources\Notificaciones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Notificaciones\Enums\NotificacionEstado;

class NotificacionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('plantilla.nombre')
                    ->label('Plantilla')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email_destinatario')
                    ->label('Destinatario')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('asunto_procesado')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->asunto_procesado),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof NotificacionEstado ? $state->value : $state) {
                        'en_cola' => 'warning',
                        'enviado' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state): ?string {
                        if ($state === null) {
                            return null;
                        }
                        if ($state instanceof NotificacionEstado) {
                            return $state->label();
                        }
                        return NotificacionEstado::tryFrom($state)?->label() ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('relacionado_tabla')
                    ->label('Documento')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'facturas' => 'Factura',
                        'presupuestos' => 'Presupuesto',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('relacionado_id')
                    ->label('Nº Doc.')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->relacionado_tabla || !$state) {
                            return 'N/A';
                        }

                        $numero = \DB::table($record->relacionado_tabla)
                            ->where('id', $state)
                            ->value('numero_completo');

                        return $numero ?? "ID: {$state}";
                    })
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d-m-Y H:i:s')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('fecha_envio')
                    ->label('Fecha envío')
                    ->dateTime('d-m-Y H:i:s')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('error')
                    ->label('Error')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->error)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'en_cola' => 'En cola',
                        'enviado' => 'Enviado',
                        'error' => 'Error',
                    ]),

                SelectFilter::make('relacionado_tabla')
                    ->label('Tipo documento')
                    ->options([
                        'facturas' => 'Facturas',
                        'presupuestos' => 'Presupuestos',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha', 'desc');
    }
}
