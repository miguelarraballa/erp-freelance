<?php

namespace Notificaciones\Filament\Resources\NotificacionesEtiquetas;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\Pages\CreateNotificacionEtiqueta;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\Pages\EditNotificacionEtiqueta;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\Pages\ListNotificacionesEtiquetas;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\Schemas\NotificacionEtiquetaForm;
use Notificaciones\Filament\Resources\NotificacionesEtiquetas\Tables\NotificacionesEtiquetasTable;
use Notificaciones\Models\NotificacionEtiqueta;

class NotificacionEtiquetaResource extends Resource
{
    protected static ?string $model = NotificacionEtiqueta::class;

    protected static ?string $navigationLabel = 'Etiquetas';
    protected static ?string $modelLabel = 'Etiqueta';
    protected static ?string $pluralModelLabel = 'Etiquetas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static \UnitEnum|string|null $navigationGroup = 'Empresa';
    protected static ?int $navigationSort = 91;
    protected static ?string $recordTitleAttribute = 'tag_name';

    public static function form(Schema $schema): Schema
    {
        return NotificacionEtiquetaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificacionesEtiquetasTable::configure($table);
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
            'index' => ListNotificacionesEtiquetas::route('/'),
            'create' => CreateNotificacionEtiqueta::route('/create'),
            'edit' => EditNotificacionEtiqueta::route('/{record}/edit'),
        ];
    }
}
