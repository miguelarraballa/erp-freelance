<?php

namespace App\Filament\Resources\Impuestos;

use App\Filament\Resources\Impuestos\Pages\CreateImpuesto;
use App\Filament\Resources\Impuestos\Pages\EditImpuesto;
use App\Filament\Resources\Impuestos\Pages\ListImpuestos;
use App\Filament\Resources\Impuestos\Schemas\ImpuestoForm;
use App\Filament\Resources\Impuestos\Tables\ImpuestosTable;
use App\Models\Impuesto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ImpuestoResource extends Resource
{
    protected static ?string $model = Impuesto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static \UnitEnum|string|null $navigationGroup = 'Facturacion';
    protected static ?int $navigationSort = 30;
    protected static ?string $recordTitleAttribute = 'Impuesto';

    public static function form(Schema $schema): Schema
    {
        return ImpuestoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImpuestosTable::configure($table);
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
            'index' => ListImpuestos::route('/'),
            'create' => CreateImpuesto::route('/create'),
            'edit' => EditImpuesto::route('/{record}/edit'),
        ];
    }
}
