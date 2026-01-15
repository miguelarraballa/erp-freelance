<?php

namespace App\Filament\Resources\Clientes\Schemas;


use Filament\Schemas\Schema;
use Filament\Forms\Components\{
    TextInput,
    Textarea,
    Toggle,
    DatePicker,
    Select,
};
use Filament\Schemas\Components\Html;
use Illuminate\Support\Carbon;
use App\Rules\SpanishDocIdRule;
use App\Support\SpanishDocId as DocId;
use Filament\Schemas\Components\Utilities\Get;
use Closure;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
        
            ->columns(12)
            ->schema([

                TextInput::make('nombre')->required()->maxLength(255)->columnSpan(12),
                TextInput::make('razon_social')->required()->maxLength(255)->columnSpan(6),
                TextInput::make('nif')
                    ->label("NIF/NIE/CIF")
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->helperText('Introduce un NIF, NIE o CIF válido. Ejemplo: 12345678A, X1234567B, B12345678')
                    ->label("NIF/NIE/CIF")
                    ->required()
                    ->rule(fn (Get $get) => $get('extranjero') ? null : new SpanishDocIdRule())
                    ->dehydrateStateUsing(fn ($state) => DocId::normalize((string) $state))
                    ->unique(table: 'clientes', column: 'nif', ignorable: fn ($record) => $record)
                    ->maxLength(20)
                    ->columnSpan(4),
                Toggle::make('extranjero')
                    ->label('Extranjero')
                    ->live()
                    ->default(false)
                    ->columnSpan(2),    
                TextInput::make('direccion')->required()->maxLength(255)->columnSpan(12),
                TextInput::make('cp')->required()->label('Código postal')->maxLength(10)->columnSpan(6),
                TextInput::make('ciudad')->required()->columnSpan(6),
                TextInput::make('provincia')->required()->columnSpan(6),
                TextInput::make('pais')->required()->default('España')->columnSpan(6),

                Html::make('<hr/>')->columnSpanFull(),
                
                TextInput::make('contacto_nombre')->label('Persona de contacto')->columnSpan(12),
                TextInput::make('contacto_email')->email()->columnSpan(6),
                TextInput::make('contacto_telefono')->columnSpan(6),
                
                Html::make('<hr/>')->columnSpanFull(),
                
                TextInput::make('iban')->maxLength(34)->columnSpan(12),
                TextInput::make('email_facturacion')->email()->columnSpan(6),
                TextInput::make('telefono_facturacion')->columnSpan(6),
                
                Html::make('<hr/>')->columnSpanFull(),

                Toggle::make('cliente')
                ->label('Cliente')
                ->live() // para refrescar validación al cambiar
                ->default(fn () =>
                    request()->routeIs('filament.*.resources.clientes.create')
                    || request()->is('*clientes/create')
                )
                ->rules([
                    // boolean básico
                    'boolean',
                    // regla cruzada: al menos uno de los dos en true
                    fn (Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                        $isCliente   = (bool) $get('cliente');
                        $isProveedor = (bool) $get('proveedor');
                        if (! $isCliente && ! $isProveedor) {
                            $fail('Marca al menos “Cliente” o “Proveedor”.');
                        }
                    },
                ])
                ->columnSpan(2),
                
                TextInput::make('codigo_cliente')->readonly()->columnSpan(4),

                Toggle::make('proveedor')
                    ->label('Proveedor')
                    ->live()
                    ->default(fn () =>
                        request()->routeIs('filament.*.resources.proveedors.create')
                        || request()->is('*proveedors/create')               // slug por defecto de Filament
                        || request()->is('*proveedores/proveedors/create')
                    )
                    ->rules([
                        'boolean',
                        fn (Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                            $isCliente   = (bool) $get('cliente');
                            $isProveedor = (bool) $get('proveedor');
                            if (! $isCliente && ! $isProveedor) {
                                $fail('Marca al menos “Cliente” o “Proveedor”.');
                            }
                        },
                    ])
                    ->columnSpan(2),
                TextInput::make('codigo_proveedor')->readonly()->columnSpan(4),

                DatePicker::make('fecha_alta')->required()->columnSpan(3),
                DatePicker::make('fecha_baja')->columnSpan(3),
                Toggle::make('activo')->default(true)->columnSpan(3),
                Toggle::make('irpf')->label('Sujeto a IRPF')->columnSpan(3),

                Html::make('<hr/>')->columnSpanFull(),

                Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->columnSpan(12),

                Html::make('<hr/>')->columnSpanFull(),

                Textarea::make('observaciones')->rows(3)->columnSpan(12),

            ]);
    }
}
