<?php

namespace App\Filament\Resources\Proveedores\Pages;

use App\Filament\Resources\Proveedores\ProveedorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProveedor extends CreateRecord
{
    protected static string $resource = ProveedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['cliente']) && empty($data['proveedor'])) {
            throw ValidationException::withMessages([
                'cliente'   => 'Marca al menos “Cliente” o “Proveedor”.',
                'proveedor' => 'Marca al menos “Cliente” o “Proveedor”.',
            ]);
        }

        $data['nif'] = \App\Support\SpanishDocId::normalize($data['nif'] ?? '');

        if (!empty($data['cliente']) && empty($data['codigo_cliente'])) {
            $data['codigo_cliente'] = \App\Services\CodigoSecuencialService::next('cliente', 'C', 4);
        }
        
        if (!empty($data['proveedor']) && empty($data['codigo_proveedor'])) {
            $data['codigo_proveedor'] = \App\Services\CodigoSecuencialService::next('proveedor', 'P', 4);
        }

        
        return $data;
    }
}
