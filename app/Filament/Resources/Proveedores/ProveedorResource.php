<?php

namespace App\Filament\Resources\Proveedores;

use App\Filament\Resources\Clientes\Schemas\ClienteForm;
use App\Filament\Resources\Clientes\Tables\ClientesTable;
use App\Filament\Resources\Proveedores\Pages\{CreateProveedor, EditProveedor, ListProveedores};
use App\Models\Cliente;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class ProveedorResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static \UnitEnum|string|null $navigationGroup = 'Proveedores';
    protected static ?string $navigationLabel = 'Proveedores';
    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return ClienteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientesTable::configure($table);
    }

    public static function getEloquentQuery(): EloquentBuilder
    {
        return Cliente::query()->where('proveedor', 1);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProveedores::route('/'),
            'create' => CreateProveedor::route('/create'),
            'edit'   => EditProveedor::route('/{record}/edit'),
        ];
    }
}