<?php

namespace Notificaciones\Filament\Resources\Notificaciones\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Notificaciones\Enums\NotificacionEstado;

class NotificacionViewSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                        Select::make('notificacion_plantilla_id')
                            ->label('Plantilla')
                            ->relationship('plantilla', 'nombre')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(6),

                        TextInput::make('email_destinatario')
                            ->label('Email destinatario')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(6),

                        DateTimePicker::make('fecha')
                            ->label('Fecha')
                            ->disabled()
                            ->dehydrated()
                            ->format('d-m-Y H:i:s')
                            ->columnSpan(4),

                        Select::make('estado')
                            ->options(collect(NotificacionEstado::cases())->mapWithKeys(
                                fn (NotificacionEstado $estado) => [$estado->value => $estado->label()]
                            )->all())
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(4),

                        DateTimePicker::make('fecha_envio')
                            ->label('Fecha de envío')
                            ->disabled()
                            ->dehydrated()
                            ->format('d-m-Y H:i:s')
                            ->columnSpan(4),

                        Select::make('relacionado_tabla')
                            ->label('Relacionado con')
                            ->options([
                                'facturas' => 'Facturas',
                                'presupuestos' => 'Presupuestos',
                            ])
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(6),

                        Placeholder::make('numero_documento')
                            ->label('Número de documento')
                            ->content(function ($record) {
                                if (!$record->relacionado_tabla || !$record->relacionado_id) {
                                    return 'N/A';
                                }

                                $numero = \DB::table($record->relacionado_tabla)
                                    ->where('id', $record->relacionado_id)
                                    ->value('numero_completo');

                                return $numero ?? "ID: {$record->relacionado_id}";
                            })
                            ->columnSpan(6),
                        Textarea::make('asunto_procesado')
                            ->label('Asunto')
                            ->rows(2)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),

                        Placeholder::make('cuerpo_html_preview')
                            ->label('Vista previa del contenido HTML')
                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                '<div class="border border-gray-300 dark:border-gray-600 p-6 rounded-lg bg-white dark:bg-gray-900 text-black dark:text-white">' .
                                $record->cuerpo_html_procesado .
                                '</div>'
                            ))
                            ->columnSpanFull(),

                        Textarea::make('cuerpo_html_procesado')
                            ->label('Código HTML')
                            ->rows(8)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->helperText('Código HTML del email'),

                        Textarea::make('cuerpo_texto_procesado')
                            ->label('Versión texto plano')
                            ->rows(6)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->cuerpo_texto_procesado)),
                        Textarea::make('error')
                            ->label('Mensaje de error')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->estado === NotificacionEstado::Error),
            ]);
    }
}
