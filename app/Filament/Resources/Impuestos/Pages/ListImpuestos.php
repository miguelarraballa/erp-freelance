<?php

namespace App\Filament\Resources\Impuestos\Pages;

use App\Filament\Resources\Impuestos\ImpuestoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImpuestos extends ListRecords
{
    protected static string $resource = ImpuestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
