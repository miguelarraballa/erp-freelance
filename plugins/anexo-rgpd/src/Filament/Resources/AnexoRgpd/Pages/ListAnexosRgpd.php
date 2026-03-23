<?php

namespace AnexoRgpd\Filament\Resources\AnexoRgpd\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use AnexoRgpd\Filament\Resources\AnexoRgpd\AnexoRgpdResource;

class ListAnexosRgpd extends ListRecords
{
    protected static string $resource = AnexoRgpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
