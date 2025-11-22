<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SerieResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;
use Illuminate\Support\Facades\DB;

class CreateSerie extends CreateRecord
{
    protected static string $resource = SerieResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // guardar normal

            Actions\Action::make('crearYActivar')
                ->label('Crear y activar')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Crear y activar esta serie')
                ->modalDescription('Activarla desactivará la serie actualmente activa del mismo tipo y ejercicio. ¿Continuar?')
                ->action(function () {
                    DB::transaction(function () {
                        // Crea el registro a partir del formulario
                        $data = $this->form->getState();

                        /** @var \App\Models\Serie $serie */
                        $serie = static::getModel()::create($data);

                        // Desactiva hermanas y activa la nueva
                        \App\Models\Serie::where('tipo', $serie->tipo)
                            ->where('ejercicio', $serie->ejercicio)
                            ->whereKeyNot($serie->id)
                            ->update(['activo' => false]);

                        $serie->activo = true;
                        $serie->save();

                        // redirige a edición
                        $this->redirect(SerieResource::getUrl('edit', ['record' => $serie]));
                    });

                    Notification::make()
                        ->title('Serie creada y activada.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
