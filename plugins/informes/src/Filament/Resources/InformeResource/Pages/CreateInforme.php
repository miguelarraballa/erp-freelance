<?php

namespace Informes\Filament\Resources\InformeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Informes\Filament\Resources\InformeResource;

class CreateInforme extends CreateRecord
{
    protected static string $resource = InformeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
