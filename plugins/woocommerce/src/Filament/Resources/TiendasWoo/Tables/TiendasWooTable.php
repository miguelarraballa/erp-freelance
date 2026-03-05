<?php

namespace Woocommerce\Filament\Resources\TiendasWoo\Tables;

use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

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
                    ->action(function ($record, $livewire) {
                        $exitCode = Artisan::call('woo:import', ['--tienda' => $record->id]);

                        $output = Artisan::output();

                        if ($exitCode === 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Importación completada')
                                ->body(trim($output))
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Importación con errores')
                                ->body(trim($output))
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('nombre');
    }
}
