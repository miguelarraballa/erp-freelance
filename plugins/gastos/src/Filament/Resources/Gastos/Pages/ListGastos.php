<?php

namespace Gastos\Filament\Resources\Gastos\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Gastos\Filament\Resources\Gastos\GastoResource;

class ListGastos extends ListRecords
{
    protected static string $resource = GastoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
