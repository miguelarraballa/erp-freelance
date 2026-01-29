<?php

namespace Gastos\Filament\Resources\Gastos;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Gastos\Filament\Resources\Gastos\Pages\CreateGasto;
use Gastos\Filament\Resources\Gastos\Pages\EditGasto;
use Gastos\Filament\Resources\Gastos\Pages\ListGastos;
use Gastos\Filament\Resources\Gastos\Schemas\GastoForm;
use Gastos\Filament\Resources\Gastos\Tables\GastosTable;
use Gastos\Models\Gasto;

class GastoResource extends Resource
{
    protected static ?string $model = Gasto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static \UnitEnum|string|null $navigationGroup = 'Empresa';
    protected static ?string $navigationLabel = 'Gastos no facturables';
    protected static ?int $navigationSort = 40;

    protected static ?string $pluralModelLabel = 'Gastos';
    protected static ?string $modelLabel = 'Gasto';
    protected static ?string $breadcrumb = 'Gastos';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return GastoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GastosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGastos::route('/'),
            'create' => CreateGasto::route('/create'),
            'edit' => EditGasto::route('/{record}/edit'),
        ];
    }
}
