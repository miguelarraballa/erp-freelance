<?php

namespace App\Filament\Resources\FacturasProveedores\Pages;

use App\Filament\Resources\FacturasProveedores\FacturasProveedorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFacturasProveedores extends ListRecords
{
    protected static string $resource = FacturasProveedorResource::class;

    public function getTitle(): string
    {
        return 'Facturas de Proveedores'; 
    }
    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
