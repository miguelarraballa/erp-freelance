<?php

namespace Woocommerce\Filament\Resources\TiendasWoo;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Woocommerce\Filament\Resources\TiendasWoo\Pages\CreateTiendaWoo;
use Woocommerce\Filament\Resources\TiendasWoo\Pages\EditTiendaWoo;
use Woocommerce\Filament\Resources\TiendasWoo\Pages\ListTiendasWoo;
use Woocommerce\Filament\Resources\TiendasWoo\Schemas\TiendaWooForm;
use Woocommerce\Filament\Resources\TiendasWoo\Tables\TiendasWooTable;
use Woocommerce\Models\TiendaWoo;

class TiendaWooResource extends Resource
{
    protected static ?string $model = TiendaWoo::class;

    protected static ?string $navigationLabel      = 'Tiendas WooCommerce';
    protected static ?string $modelLabel           = 'Tienda WooCommerce';
    protected static ?string $pluralModelLabel     = 'Tiendas WooCommerce';
    protected static string|BackedEnum|null $navigationIcon  = Heroicon::OutlinedShoppingCart;
    protected static \UnitEnum|string|null  $navigationGroup = 'Empresa';
    protected static ?int $navigationSort          = 80;
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return TiendaWooForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TiendasWooTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTiendasWoo::route('/'),
            'create' => CreateTiendaWoo::route('/create'),
            'edit'   => EditTiendaWoo::route('/{record}/edit'),
        ];
    }
}
