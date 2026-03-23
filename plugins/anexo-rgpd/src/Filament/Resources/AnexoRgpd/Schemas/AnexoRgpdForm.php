<?php

namespace AnexoRgpd\Filament\Resources\AnexoRgpd\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\{Get, Set};
use Filament\Forms\Components\{
    TextInput,
    Textarea,
    DatePicker,
    Select,
    CheckboxList,
};
use App\Models\Cliente;
use AnexoRgpd\Models\AnexoRgpd;
use Illuminate\Database\Eloquent\Builder;

class AnexoRgpdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([

                // ─── DATOS DEL CLIENTE ───────────────────────────────────────
                Section::make('Datos del cliente')
                    ->columns(12)
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship(
                                name: 'cliente',
                                titleAttribute: 'mostrar',
                                modifyQueryUsing: fn (Builder $query) =>
                                    $query->where('clientes.cliente', 1)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull()
                            ->live(onBlur: false)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $id = is_numeric($state) ? (int) $state : 0;

                                if (!$id) {
                                    $set('cliente_nombre', null);
                                    $set('cliente_nif', null);
                                    $set('cliente_direccion', null);
                                    $set('cliente_email', null);
                                    return;
                                }

                                $c = Cliente::find($id);
                                if (!$c) return;

                                $set('cliente_nombre', $c->razon_social ?: $c->nombre);
                                $set('cliente_nif', $c->nif);

                                $parts = array_filter([
                                    $c->direccion,
                                    trim(($c->cp ? "{$c->cp} " : '') . ($c->ciudad ?: '')),
                                    trim(($c->provincia ? "{$c->provincia} " : '') . ($c->pais ?: '')),
                                ]);
                                $set('cliente_direccion', implode(', ', $parts));

                                $set('cliente_email', $c->email_facturacion ?: $c->contacto_email);
                            }),

                        TextInput::make('cliente_nombre')
                            ->label('Nombre / razón social')
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('cliente_nif')
                            ->label('NIF/CIF')
                            ->columnSpan(6),

                        TextInput::make('cliente_direccion')
                            ->label('Dirección')
                            ->columnSpanFull(),

                        TextInput::make('cliente_email')
                            ->label('Email')
                            ->email()
                            ->columnSpan(6),

                        TextInput::make('cliente_firmante')
                            ->label('Firmante')
                            ->columnSpan(6),

                        TextInput::make('cliente_cargo')
                            ->label('Cargo')
                            ->columnSpan(6),
                    ])
                    ->columnSpanFull(),

                // ─── SERVICIO ────────────────────────────────────────────────
                Section::make('Servicio')
                    ->columns(12)
                    ->schema([
                        Textarea::make('descripcion_servicio')
                            ->label('Descripción del servicio')
                            ->rows(3)
                            ->columnSpanFull(),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(6),

                        TextInput::make('duracion_acceso')
                            ->label('Duración del acceso')
                            ->default('1 año')
                            ->columnSpan(6),
                    ])
                    ->columnSpanFull(),

                // ─── ACCESOS ─────────────────────────────────────────────────
                Section::make('Accesos autorizados')
                    ->columns(12)
                    ->schema([
                        CheckboxList::make('accesos')
                            ->label('Sistemas y entornos autorizados')
                            ->options(AnexoRgpd::accesoLabels())
                            ->columns(2)
                            ->columnSpanFull()
                            ->live(),

                        TextInput::make('accesos_otros')
                            ->label('Detalle "Otros"')
                            ->placeholder('Especificar si se seleccionó Otros...')
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => in_array('otros', (array) ($get('accesos') ?? []))),
                    ])
                    ->columnSpanFull(),

                // ─── OBSERVACIONES ───────────────────────────────────────────
                Section::make('Observaciones')
                    ->schema([
                        Textarea::make('observaciones')
                            ->label('')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
