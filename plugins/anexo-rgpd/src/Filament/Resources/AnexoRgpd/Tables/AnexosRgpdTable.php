<?php

namespace AnexoRgpd\Filament\Resources\AnexoRgpd\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use AnexoRgpd\Models\AnexoRgpd;

class AnexosRgpdTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cliente.mostrar')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion_servicio')
                    ->label('Servicio')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('fecha_inicio')
                    ->label('Fecha inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('duracion_acceso')
                    ->label('Duración'),

                TextColumn::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (AnexoRgpd $record): string => route('anexo-rgpd.pdf', $record))
                    ->openUrlInNewTab()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
