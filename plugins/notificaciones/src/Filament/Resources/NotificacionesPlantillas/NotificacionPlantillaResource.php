<?php

namespace Notificaciones\Filament\Resources\NotificacionesPlantillas;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Notificaciones\Filament\Resources\NotificacionesPlantillas\Pages\CreateNotificacionPlantilla;
use Notificaciones\Filament\Resources\NotificacionesPlantillas\Pages\EditNotificacionPlantilla;
use Notificaciones\Filament\Resources\NotificacionesPlantillas\Pages\ListNotificacionesPlantillas;
use Notificaciones\Filament\Resources\NotificacionesPlantillas\Schemas\NotificacionPlantillaForm;
use Notificaciones\Filament\Resources\NotificacionesPlantillas\Tables\NotificacionesPlantillasTable;
use Notificaciones\Models\NotificacionPlantilla;

class NotificacionPlantillaResource extends Resource
{
    protected static ?string $model = NotificacionPlantilla::class;

    protected static ?string $navigationLabel = 'Plantillas de Notificaciones';
    protected static ?string $modelLabel = 'Plantilla de Notificación';
    protected static ?string $pluralModelLabel = 'Plantillas de Notificaciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static \UnitEnum|string|null $navigationGroup = 'Empresa';
    protected static ?int $navigationSort = 90;
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return NotificacionPlantillaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificacionesPlantillasTable::configure($table);
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
            'index' => ListNotificacionesPlantillas::route('/'),
            'create' => CreateNotificacionPlantilla::route('/create'),
            'edit' => EditNotificacionPlantilla::route('/{record}/edit'),
        ];
    }
}
