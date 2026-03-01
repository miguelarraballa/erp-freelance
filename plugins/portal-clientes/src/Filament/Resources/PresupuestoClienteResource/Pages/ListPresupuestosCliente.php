<?php

namespace PortalClientes\Filament\Resources\PresupuestoClienteResource\Pages;

use Filament\Resources\Pages\ListRecords;
use PortalClientes\Filament\Resources\PresupuestoClienteResource;

class ListPresupuestosCliente extends ListRecords
{
    protected static string $resource = PresupuestoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
