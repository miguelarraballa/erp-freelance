<?php

namespace Proyectos\Filament\Resources\Proyectos;

use Proyectos\Filament\Resources\Proyectos\Pages\CreateProyecto;
use Proyectos\Filament\Resources\Proyectos\Pages\EditProyecto;
use Proyectos\Filament\Resources\Proyectos\Pages\ListProyectos;
use Proyectos\Filament\Resources\Proyectos\RelationManagers\ProyectoTareasRelationManager;
use Proyectos\Filament\Resources\Proyectos\Schemas\ProyectoForm;
use Proyectos\Filament\Resources\Proyectos\Tables\ProyectosTable;
use Proyectos\Models\Proyecto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProyectoResource extends Resource
{
    protected static ?string $model = Proyecto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;
    protected static \UnitEnum|string|null $navigationGroup = 'Clientes';
    protected static ?string $navigationLabel = 'Proyectos';
    protected static ?int $navigationSort = 30;
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ProyectoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProyectosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProyectoTareasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProyectos::route('/'),
            'create' => CreateProyecto::route('/create'),
            'edit' => EditProyecto::route('/{record}/edit'),
        ];
    }
}
