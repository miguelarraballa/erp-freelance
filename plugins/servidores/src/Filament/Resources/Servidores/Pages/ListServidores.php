<?php

namespace Servidores\Filament\Resources\Servidores\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Servidores\Filament\Resources\Servidores\ServidorResource;

class ListServidores extends ListRecords
{
    protected static string $resource = ServidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
