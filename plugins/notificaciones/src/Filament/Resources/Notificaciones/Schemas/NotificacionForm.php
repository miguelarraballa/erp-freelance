<?php

namespace Notificaciones\Filament\Resources\Notificaciones\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Notificaciones\Enums\NotificacionEstado;

class NotificacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                Select::make('notificacion_plantilla_id')
                    ->label('Plantilla')
                    ->relationship('plantilla', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(6),
                DateTimePicker::make('fecha')
                    ->required()
                    ->columnSpan(6),
                Select::make('estado')
                    ->options(collect(NotificacionEstado::cases())->mapWithKeys(
                        fn (NotificacionEstado $estado) => [$estado->value => $estado->label()]
                    )->all())
                    ->required()
                    ->default(NotificacionEstado::EnCola->value)
                    ->columnSpan(4),
                DateTimePicker::make('fecha_envio')
                    ->label('Fecha de envio')
                    ->columnSpan(4),
                TextInput::make('relacionado_tabla')
                    ->label('Relacionado con')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan(4),
                TextInput::make('relacionado_id')
                    ->label('ID relacionado')
                    ->numeric()
                    ->required()
                    ->columnSpan(4),
                KeyValue::make('datos')
                    ->label('Datos de plantilla')
                    ->columnSpanFull(),
                Textarea::make('error')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
