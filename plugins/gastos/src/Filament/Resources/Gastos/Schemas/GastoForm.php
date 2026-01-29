<?php

namespace Gastos\Filament\Resources\Gastos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class GastoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(8),

                Select::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'Ocio' => 'Ocio',
                        'Trabajo' => 'Trabajo',
                        'Casa' => 'Casa',
                        'Seguros' => 'Seguros',
                        'Impuestos' => 'Impuestos',
                        'Crédito' => 'Crédito',
                        'Otros' => 'Otros',
                    ])
                    ->required()
                    ->columnSpan(4),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(4)
                    ->columnSpan(12),

                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->required()
                    ->default(fn () => now()->toDateString())
                    ->columnSpan(6),

                TextInput::make('importe')
                    ->label('Importe')
                    ->required()
                    ->numeric()
                    ->columnSpan(6),
            ]);
    }
}
