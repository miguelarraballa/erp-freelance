<?php

namespace App\Filament\Resources\Impuestos\Schemas;

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
use App\Forms\Components\CountrySelect;

class ImpuestoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
                ->columns(12)
                ->schema([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(4),
                Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'iva'       => 'IVA',
                        'irpf'      => 'IRPF',
                        'exento'    => 'Exento',
                        'otros'     => 'Otros',
                    ])
                    ->columnSpan(3)
                    ->searchable(),
                TextInput::make('porcentaje')
                    ->numeric()->minValue(0)->step('1')->maxValue(100)
                    ->label('Porcentaje')
                    ->columnSpan(2),
                CountrySelect::make('pais')            // usa 'pais' por defecto
                    ->preferred(['ES'])
                    ->columnSpan(3),
                Toggle::make('activo')->default(1),


            ]);
    }
}
