<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClienteResource;
use App\Models\Cliente;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    // Esta página sí aparece en el menú
    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return Cliente::query()->where('cliente', 1);
    }
}