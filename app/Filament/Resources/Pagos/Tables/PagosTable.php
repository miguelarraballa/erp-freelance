<?php

namespace App\Filament\Resources\Pagos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Pago;
use Filament\Support\Icons\Heroicon;

class PagosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_pago', 'desc')
            ->columns([
                TextColumn::make('numero_completo')
                    ->label('Factura')
                    ->getStateUsing(fn ($record) => $record->factura->numero_completo)
                    ->sortable(),
                TextColumn::make('fecha_pago')
                    ->date("d-m-Y")
                    ->sortable(),
                TextColumn::make('importe')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pdf')
                    ->label('PDF')
                    ->getStateUsing(fn (Pago $record): ?int => $record->justificante_path ? 1 : null)
                    ->formatStateUsing(fn (): string => '')
                    ->icon(fn (Pago $record) => $record->justificante_path !== null ? Heroicon::OutlinedArrowDownTray : null)
                    ->url(fn (Pago $record): ?string => $record->justificante_path !== null ? route('pagos.justificante', $record) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->tooltip(fn (Pago $record): ?string => $record->justificante_path ? 'Descargar PDF' : null)
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
            ]);
    }
}
