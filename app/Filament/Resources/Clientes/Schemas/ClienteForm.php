<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{
    TextInput,
    Textarea,
    Toggle,
    DatePicker,
    Select
};

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('nombre')->required()->maxLength(255),
            TextInput::make('razon_social')->maxLength(255),
            TextInput::make('nif')->required()->unique(ignoreRecord: true)->maxLength(20),
            TextInput::make('direccion')->maxLength(255),
            TextInput::make('cp')->label('Código postal')->maxLength(10),
            TextInput::make('ciudad'),
            TextInput::make('provincia'),
            TextInput::make('pais')->default('España'),

            TextInput::make('contacto_nombre')->label('Persona de contacto'),
            TextInput::make('contacto_email')->email(),
            TextInput::make('contacto_telefono'),

            TextInput::make('iban')->maxLength(34),
            TextInput::make('email_facturacion')->email(),
            TextInput::make('telefono_facturacion'),
            TextInput::make('codigo_cliente'),

            Toggle::make('irpf')->label('Sujeto a IRPF'),
            Toggle::make('cliente'),
            Toggle::make('proveedor'),
            Toggle::make('activo')->default(true),

            Select::make('user_id')
                ->label('Usuario')
                ->relationship('user', 'email')
                ->searchable()
                ->preload()
                ->nullable(),

            Textarea::make('observaciones')->rows(3),
            DatePicker::make('fecha_alta'),
            DatePicker::make('fecha_baja'),
        ]);
    }
}