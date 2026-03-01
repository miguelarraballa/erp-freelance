<?php

namespace App\Filament\Resources\UsuariosCliente\Pages;

use App\Filament\Resources\UsuariosCliente\UsuarioClienteResource;
use App\Models\Cliente;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUsuarioCliente extends EditRecord
{
    protected static string $resource = UsuarioClienteResource::class;

    public ?int $pendingClienteId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingClienteId = isset($data['cliente_id']) ? (int) $data['cliente_id'] : null;
        unset($data['cliente_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles(['cliente']);

        // Desasignar de cualquier cliente previo distinto al seleccionado
        Cliente::where('user_id', $this->record->id)
            ->when($this->pendingClienteId, fn ($q) => $q->where('id', '!=', $this->pendingClienteId))
            ->update(['user_id' => null]);

        if ($this->pendingClienteId) {
            Cliente::where('id', $this->pendingClienteId)->update(['user_id' => $this->record->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
