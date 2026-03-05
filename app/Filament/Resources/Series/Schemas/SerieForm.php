<?php

namespace App\Filament\Resources\Series\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{
    TextInput,
    Textarea,
    Toggle,
    DatePicker,
    Select,
    Repeater,
    Placeholder,
    ToggleButtons,
};
use Illuminate\Support\Carbon;

class SerieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                TextInput::make('prefijo')
                    ->maxLength(10)
                    ->columnSpan(3),
                TextInput::make('codigo')
                    ->maxLength(10)
                    ->columnSpan(4),
                TextInput::make('sufijo')
                    ->maxLength(10)
                    ->columnSpan(3),
                TextInput::make('siguiente_numero')
                    ->numeric()->minValue(1)->step('1')
                    ->label('Siguiente Número')
                    ->default(1)
                    ->columnSpan(2),
       
                Select::make('tipo')
                    ->label("Serie para la factura tipo:")
                    ->options([
                        "normal"          => "Normal",
                        "rectificativa"   => "Rectificativa",
                        "abono"           => "Abono",
                        "proveedor"       => "Proveedor",
                        "presupuesto"     => "Presupuesto",
                        "simplificada"    => "Simplificada (WooCommerce)",
                    ])
                    ->default("null")
                    ->columnSpan(3),
                TextInput::make('ejercicio')
                    ->label('Ejercicio')
                    ->numeric()->minValue(2026)->maxValue(2100)
                    ->default(fn () => (int) now()->year)
                    ->required()
                    ->columnSpan(3),
                Toggle::make('por_defecto')->default(0)->columnSpan(2), 
            ]);

    }
}
