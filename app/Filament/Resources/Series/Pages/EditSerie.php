<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SerieResource;
use App\Models\Serie;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\{Action, DeleteAction}; 
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditSerie extends EditRecord
{
    protected static string $resource = SerieResource::class;

    protected function getHeaderActions(): array
    {
        $enUso = fn () => $this->record?->facturas()->exists() ?? false;

        return [
            Action::make('activar')
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

                        Serie::where('tipo', $serie->tipo)
                            ->where('ejercicio', $serie->ejercicio)
                            ->whereKeyNot($serie->id)
                            ->update(['activo' => false]);

                        $serie->forceFill(['activo' => true])->save();
                    });

                    Notification::make()->title('Serie activada correctamente.')->success()->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Action::make('desactivar')
                ->label('Desactivar serie')
                ->icon('heroicon-o-pause-circle')
                ->color('gray')
                ->visible(fn (Serie $record) => $record->activo)
                ->requiresConfirmation()
                ->modalHeading('Desactivar esta serie')
                ->modalDescription(fn (Serie $record) =>
                    "Esta serie dejará de estar activa para “{$record->tipo} - {$record->ejercicio}”.")
                ->action(function () {
                    $this->record->forceFill(['activo' => false])->save();

                    Notification::make()->title('Serie desactivada.')->success()->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            DeleteAction::make()->hidden($enUso),
        ];
    }
}