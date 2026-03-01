<?php

namespace App\Filament\Resources\UsuariosCliente\Pages;

use App\Filament\Resources\UsuariosCliente\UsuarioClienteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsuariosCliente extends ListRecords
{
    protected static string $resource = UsuarioClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
