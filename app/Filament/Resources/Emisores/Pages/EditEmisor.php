<?php

namespace App\Filament\Resources\Emisores\Pages;

use App\Filament\Resources\Emisores\EmisorResource;
use Filament\Resources\Pages\EditRecord;

class EditEmisor extends EditRecord
{
    protected static string $resource = EmisorResource::class;

    // Botones del formulario (pie del form)
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),   // Guardar
            $this->getCancelFormAction(), // Cancelar
        ];
    }

    // Nada en cabecera (ni borrar)
    protected function getHeaderActions(): array
    {
        return [];
    }
}