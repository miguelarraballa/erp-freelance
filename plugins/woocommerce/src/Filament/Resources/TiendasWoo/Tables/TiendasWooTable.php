<?php

namespace Woocommerce\Filament\Resources\TiendasWoo\Tables;

use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Woocommerce\Services\WoocommerceApiService;
use Woocommerce\Services\WooOrderImportService;

class TiendasWooTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->limit(40),

                TextColumn::make('serie.prefijo')
                    ->label('Serie')
                    ->formatStateUsing(fn ($record) =>
                        ($record->serie->prefijo ?? '') .
                        ($record->serie->codigo ?? '') .
                        ($record->serie->sufijo ?? '')
                    ),

                TextColumn::make('cliente.mostrar')
                    ->label('Cliente genérico'),

                TextColumn::make('ultima_sincronizacion')
                    ->label('Última sync')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Nunca'),

                IconColumn::make('activo')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('importar_ahora')
                    ->label('Importar ahora')
                    ->icon(Heroicon::OutlinedArrowDownOnSquare)
                    ->requiresConfirmation()
                    ->modalHeading('Importar pedidos')
                    ->modalDescription(fn ($record) => "¿Importar pedidos nuevos de \"{$record->nombre}\"?")
                    ->action(function ($record) {
                        try {
                            $api = new WoocommerceApiService($record);
                            $orders = $api->getAllOrdersSinceLastSync();
                            $importer = new WooOrderImportService($record);
                            $stats = $importer->importarPedidos($orders);
                            $record->update(['ultima_sincronizacion' => now()]);

                            \Filament\Notifications\Notification::make()
                                ->title('Importación completada')
                                ->body("{$stats['importados']} facturas, {$stats['abonos']} abonos, {$stats['errores']} errores")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error en la importación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('nombre');
    }
}
