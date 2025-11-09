<?php

namespace App\Filament\Resources\FacturasProveedores\Pages;

use App\Filament\Resources\FacturasProveedores\FacturasProveedorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFacturasProveedor extends EditRecord
{
    protected static string $resource = FacturasProveedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
