<?php

namespace App\Filament\Resources\Emisores\Pages;

use App\Filament\Resources\Emisores\EmisorResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Emisor;
use Filament\Notifications\Notification;

class CreateEmisor extends CreateRecord
{
    protected static string $resource = EmisorResource::class;

    public function mount(): void
        {
            // Si ya existe uno, avisamos y redirigimos a editar
            if (Emisor::exists()) {
                $record = Emisor::query()->first();

                Notification::make()
                    ->title('Ya existe un emisor')
                    ->body('Solo puede haber un emisor. Te llevo a editarlo.')
                    ->warning()
                    ->send();

                $this->redirect(
                    EmisorResource::getUrl('edit', ['record' => $record])
                );

                return;
            }

            parent::mount();
        }
}