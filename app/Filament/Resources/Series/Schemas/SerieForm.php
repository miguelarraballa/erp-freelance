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
                Toggle::make('por_defecto')->default(0),
                Toggle::make('activo')->default(1),
                
            ]);

    }
}
