<?php

namespace Notificaciones\Filament\Resources\Notificaciones;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Notificaciones\Filament\Resources\Notificaciones\Pages\CreateNotificacion;
use Notificaciones\Filament\Resources\Notificaciones\Pages\EditNotificacion;
use Notificaciones\Filament\Resources\Notificaciones\Pages\ListNotificaciones;
use Notificaciones\Filament\Resources\Notificaciones\Schemas\NotificacionForm;
use Notificaciones\Filament\Resources\Notificaciones\Tables\NotificacionesTable;
use Notificaciones\Models\Notificacion;

class NotificacionResource extends Resource
{
    protected static ?string $model = Notificacion::class;

    protected static ?string $navigationLabel = 'Notificaciones';
    protected static ?string $modelLabel = 'Notificación';
    protected static ?string $pluralModelLabel = 'Notificaciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static \UnitEnum|string|null $navigationGroup = 'Empresa';
    protected static ?int $navigationSort = 89;
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return NotificacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificacionesTable::configure($table);
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
            'index' => ListNotificaciones::route('/'),
            'create' => CreateNotificacion::route('/create'),
            'edit' => EditNotificacion::route('/{record}/edit'),
        ];
    }
}
