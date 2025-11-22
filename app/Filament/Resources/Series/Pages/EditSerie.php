<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SerieResource;
use App\Models\Serie;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions; 
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditSerie extends EditRecord
{
    protected static string $resource = SerieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('activar')
                ->label('Activar serie')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->visible(fn (Serie $record) => ! $record->activo)
                ->requiresConfirmation()
                ->modalHeading('Activar esta serie')
                ->modalDescription(fn (Serie $record) =>
                    "Esto desactivará la serie actualmente activa del tipo “{$record->tipo}” para el ejercicio {$record->ejercicio}. ¿Continuar?")
                ->action(function () {
                    DB::transaction(function () {
                        /** @var Serie $serie */
                        $serie = $this->record->refresh();

                        // Desactiva hermanas del mismo tipo+ejercicio
                        Serie::where('tipo', $serie->tipo)
                            ->where('ejercicio', $serie->ejercicio)
                            ->whereKeyNot($serie->id)
                            ->update(['activo' => false]);

                        // Activa la actual
                        $serie->activo = true;
                        $serie->save(); // tu hook saving volverá a desactivar si hiciera falta
                    });

                    Notification::make()
                        ->title('Serie activada correctamente.')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\Action::make('desactivar')
                ->label('Desactivar serie')
                ->icon('heroicon-o-pause-circle')
                ->color('gray')
                ->visible(fn (Serie $record) => $record->activo)
                ->requiresConfirmation()
                ->modalHeading('Desactivar esta serie')
                ->modalDescription(fn (Serie $record) =>
                    "Esta serie dejará de estar activa para “{$record->tipo} - {$record->ejercicio}”. No habrá serie activa hasta que actives otra.")
                ->action(function () {
                    $this->record->forceFill(['activo' => false])->save();
                    Notification::make()
                        ->title('Serie desactivada.')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            // Acciones estándar
            Actions\DeleteAction::make(),
        ];
    }
}
