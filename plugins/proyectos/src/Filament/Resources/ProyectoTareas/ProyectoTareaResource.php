<?php

namespace Proyectos\Filament\Resources\ProyectoTareas;

use Proyectos\Filament\Resources\ProyectoTareas\Pages\CreateProyectoTarea;
use Proyectos\Filament\Resources\ProyectoTareas\Pages\EditProyectoTarea;
use Proyectos\Filament\Resources\ProyectoTareas\Pages\ListProyectoTareas;
use Proyectos\Filament\Resources\ProyectoTareas\Schemas\ProyectoTareaForm;
use Proyectos\Filament\Resources\ProyectoTareas\Tables\ProyectoTareasTable;
use Proyectos\Models\ProyectoTarea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProyectoTareaResource extends Resource
{
    protected static ?string $model = ProyectoTarea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static \UnitEnum|string|null $navigationGroup = 'Clientes';
    protected static ?string $navigationLabel = 'Tareas';

    protected static ?string $pluralModelLabel = 'Tareas';
    protected static ?string $modelLabel = 'Tarea'; 
    protected static ?string $breadcrumb = 'Tareas';
    
    protected static ?int $navigationSort = 31;
    protected static ?string $recordTitleAttribute = 'descripcion';

    public static function form(Schema $schema): Schema
    {
        return ProyectoTareaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProyectoTareasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProyectoTareas::route('/'),
            'create' => CreateProyectoTarea::route('/create'),
            'edit' => EditProyectoTarea::route('/{record}/edit'),
        ];
    }
}
