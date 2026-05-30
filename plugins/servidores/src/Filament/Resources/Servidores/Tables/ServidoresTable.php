<?php

namespace Servidores\Filament\Resources\Servidores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Servidores\Models\Servidor;

class ServidoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_renovacion', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->orderByDesc('activo'))
            ->columns([
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('fecha_renovacion')
                    ->label('Renovación')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Servidor $record): string => match (true) {
                        $record->fecha_renovacion->isPast()                          => 'danger',
                        $record->fecha_renovacion->isBefore(now()->addDays(30))      => 'warning',
                        default                                                      => 'success',
                    }),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Servidor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('paquete')
                    ->label('Paquete')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('fecha_alta')
                    ->label('Alta')
                    ->date('d/m/Y')
                    ->sortable(),

                
            ])
            ->filters([
                TernaryFilter::make('activo')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos')
                    ->placeholder('Todos'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
