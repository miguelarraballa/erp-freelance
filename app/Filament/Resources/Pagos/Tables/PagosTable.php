<?php

namespace App\Filament\Resources\Pagos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                Filter::make('fecha_rango')
                    ->label('Fecha')
                    ->form([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'] ?? null, fn ($q, $date) => $q->whereDate('fecha_pago', '>=', $date))
                            ->when($data['hasta'] ?? null, fn ($q, $date) => $q->whereDate('fecha_pago', '<=', $date));
                    }),
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
