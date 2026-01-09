<?php

namespace Presupuestos\src\Filament\Resources\Presupuestos;

use Prespuestos\src\Filament\Resources\Presupuestos\Pages\CreateFactura;
use Prespuestos\src\Filament\Resources\Presupuestos\EditFactura;
use Prespuestos\src\Filament\Resources\Presupuestos\ListFacturas;
use Prespuestos\src\Filament\Resources\Presupuestos\Schemas\FacturaForm;
use Prespuestos\src\Filament\Resources\Presupuestos\Tables\FacturasTable;
use Prespuestos\Models\Presupuesto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PresupuestoResource extends Resource
{
    protected static ?string $model = Presupuesto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static \UnitEnum|string|null $navigationGroup = 'Comercial';
    protected static ?int $navigationSort = 32;
    protected static ?string $recordTitleAttribute = 'Presupuesto';

    public static function form(Schema $schema): Schema
    {
        return PresupuestoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PresupuestosTable::configure($table);
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
            'index' => ListPresupuestos::route('/'),
            'create' => CreatePresupuesto::route('/create'),
            'edit' => EditPresupuesto::route('/{record}/edit'),
        ];
    }
    public static function canDelete(Model $record): bool
    {
        return $record->estado === 'borrador';
    }
}
