<?php

namespace App\Filament\Resources\FacturasProveedores;

use App\Filament\Resources\FacturasProveedores\Pages\CreateFacturasProveedor;
use App\Filament\Resources\FacturasProveedores\Pages\EditFacturasProveedor;
use App\Filament\Resources\FacturasProveedores\Pages\ListFacturasProveedores;
use App\Filament\Resources\FacturasProveedores\Schemas\FacturasProveedorForm;
use App\Filament\Resources\FacturasProveedores\Tables\FacturasProveedoresTable;
use App\Models\FacturasProveedor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FacturasProveedorResource extends Resource
{
    protected static ?string $model = FacturasProveedor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    // en ClienteResource.php
    protected static ?string $modelLabel = 'Factura Proveedor';
    protected static ?string $pluralModelLabel = 'Facturas Proveedores';
    protected static \UnitEnum|string|null $navigationGroup = 'Proveedores';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'Facturas de Proveedores';

    public static function form(Schema $schema): Schema
    {
        return FacturasProveedorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacturasProveedoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacturasProveedores::route('/'),
            'create' => CreateFacturasProveedor::route('/create'),
            'edit' => EditFacturasProveedor::route('/{record}/edit'),
        ];
    }
}
