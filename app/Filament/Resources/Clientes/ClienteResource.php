<?php

namespace App\Filament\Resources\Clientes;

use App\Filament\Resources\Clientes\Pages\{CreateCliente, EditCliente, ListClientes};
use App\Filament\Resources\Clientes\Schemas\ClienteForm;
use App\Filament\Resources\Clientes\Tables\ClientesTable;
use App\Models\Cliente;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ClienteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientesTable::configure($table);
    }

    // Aquí se aplica el filtro global del recurso
    public static function getEloquentQuery(): Builder
    {
        return Cliente::query()->where('cliente', 1);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListClientes::route('/'),
            'create' => CreateCliente::route('/create'),
            'edit'   => EditCliente::route('/{record}/edit'),
        ];
    }
}