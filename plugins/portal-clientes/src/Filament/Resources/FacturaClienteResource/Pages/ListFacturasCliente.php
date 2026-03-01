<?php

namespace PortalClientes\Filament\Resources\FacturaClienteResource\Pages;

use Filament\Resources\Pages\ListRecords;
use PortalClientes\Filament\Resources\FacturaClienteResource;

class ListFacturasCliente extends ListRecords
{
    protected static string $resource = FacturaClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
