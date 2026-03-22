<?php

namespace Informes\Filament\Resources\InformeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Informes\Filament\Resources\InformeResource;

class EditInforme extends EditRecord
{
    protected static string $resource = InformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
