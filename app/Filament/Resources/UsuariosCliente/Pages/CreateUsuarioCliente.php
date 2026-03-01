<?php

namespace App\Filament\Resources\UsuariosCliente\Pages;

use App\Filament\Resources\UsuariosCliente\UsuarioClienteResource;
use App\Models\Cliente;
use Filament\Resources\Pages\CreateRecord;

class CreateUsuarioCliente extends CreateRecord
{
    protected static string $resource = UsuarioClienteResource::class;

    public ?int $pendingClienteId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingClienteId = isset($data['cliente_id']) ? (int) $data['cliente_id'] : null;
        unset($data['cliente_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->assignRole('cliente');

        if ($this->pendingClienteId) {
            Cliente::where('user_id', $this->record->id)->update(['user_id' => null]);
            Cliente::where('id', $this->pendingClienteId)->update(['user_id' => $this->record->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
