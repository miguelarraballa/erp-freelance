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

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([

                TextInput::make('nombre')->required()->maxLength(255)->columnSpan(12),
                TextInput::make('razon_social')->maxLength(255)->columnSpan(8),
                TextInput::make('nif')->label("NIF/NIE/CIF")->required()->unique(ignoreRecord: true)->maxLength(20)->columnSpan(4),
                TextInput::make('direccion')->maxLength(255)->columnSpan(12),
                TextInput::make('cp')->label('Código postal')->maxLength(10)->columnSpan(6),
                TextInput::make('ciudad')->columnSpan(6),
                TextInput::make('provincia')->columnSpan(6),
                TextInput::make('pais')->default('España')->columnSpan(6),

                Html::make('<hr/>')->columnSpanFull(),
                
                TextInput::make('contacto_nombre')->label('Persona de contacto')->columnSpan(12),
                TextInput::make('contacto_email')->email()->columnSpan(6),
                TextInput::make('contacto_telefono')->columnSpan(6),
                
                Html::make('<hr/>')->columnSpanFull(),
                
                TextInput::make('iban')->maxLength(34)->columnSpan(12),
                TextInput::make('email_facturacion')->email()->columnSpan(6),
                TextInput::make('telefono_facturacion')->columnSpan(6),
                TextInput::make('codigo_cliente')->columnSpan(4),

                Html::make('<hr/>')->columnSpanFull(),

                Toggle::make('irpf')->label('Sujeto a IRPF')->columnSpan(3),
                Toggle::make('cliente')->columnSpan(3),
                Toggle::make('proveedor')->columnSpan(3),
                Toggle::make('activo')->default(true)->columnSpan(3),
                DatePicker::make('fecha_alta')->columnSpan(6),
                DatePicker::make('fecha_baja')->columnSpan(6),

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