<?php

namespace Informes\Filament\Resources\InformeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Informes\Filament\Resources\InformeResource;

class ListInformes extends ListRecords
{
    protected static string $resource = InformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
