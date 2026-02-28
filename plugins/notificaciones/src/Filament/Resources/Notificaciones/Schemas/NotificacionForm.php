<?php

namespace Notificaciones\Filament\Resources\Notificaciones\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Notificaciones\Enums\NotificacionEstado;
use App\Models\Factura;
use Presupuestos\Models\Presupuesto;

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

                TextInput::make('email_destinatario')
                    ->label('Email destinatario')
                    ->email()
                    ->required()
                    ->columnSpan(6),

                DateTimePicker::make('fecha')
                    ->required()
                    ->format('d-m-Y H:i:s')
                    ->columnSpan(4),

                Select::make('estado')
                    ->options(collect(NotificacionEstado::cases())->mapWithKeys(
                        fn (NotificacionEstado $estado) => [$estado->value => $estado->label()]
                    )->all())
                    ->required()
                    ->default(NotificacionEstado::EnCola->value)
                    ->columnSpan(4),

                DateTimePicker::make('fecha_envio')
                    ->label('Fecha de envío')
                    ->format('d-m-Y H:i:s')
                    ->columnSpan(4),

                Select::make('relacionado_tabla')
                    ->label('Relacionado con')
                    ->options([
                        'facturas' => 'Facturas',
                        'presupuestos' => 'Presupuestos',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('relacionado_id', null))
                    ->columnSpan(6),

                Select::make('relacionado_id')
                    ->label('ID relacionado')
                    ->required()
                    ->searchable()
                    ->options(function (Get $get) {
                        $tabla = $get('relacionado_tabla');

                        if ($tabla === 'facturas') {
                            return Factura::query()
                                ->whereNotNull('numero_completo')
                                ->orderBy('numero_completo', 'desc')
                                ->limit(100)
                                ->pluck('numero_completo', 'id')
                                ->toArray();
                        }

                        if ($tabla === 'presupuestos') {
                            return Presupuesto::query()
                                ->whereNotNull('numero_completo')
                                ->orderBy('numero_completo', 'desc')
                                ->limit(100)
                                ->pluck('numero_completo', 'id')
                                ->toArray();
                        }

                        return [];
                    })
                    ->disabled(fn (Get $get) => empty($get('relacionado_tabla')))
                    ->helperText('Selecciona primero el tipo de documento')
                    ->columnSpan(6),

                Textarea::make('asunto_procesado')
                    ->label('Asunto (procesado)')
                    ->rows(2)
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),

                Textarea::make('cuerpo_html_procesado')
                    ->label('Cuerpo HTML (procesado)')
                    ->rows(8)
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),

                Textarea::make('error')
                    ->label('Error')
                    ->rows(3)
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),
            ]);
    }
}
