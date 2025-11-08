<?php

namespace App\Filament\Resources\Proveedores\Pages;

use App\Filament\Resources\Proveedores\ProveedorResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListProveedores extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [ Actions\CreateAction::make() ];
    }
}