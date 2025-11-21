<?php

namespace App\Filament\Resources\Clientes\Pages;
use Illuminate\Validation\ValidationException;

use App\Filament\Resources\Clientes\ClienteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

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
