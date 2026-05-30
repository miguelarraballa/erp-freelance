<?php

namespace Servidores\Filament\Resources\Servidores\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServidorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Select::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'mostrar')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(12),

                TextInput::make('nombre')
                    ->label('Nombre del servidor')
                    ->required()
                    ->maxLength(250)
                    ->columnSpan(6),

                TextInput::make('dominio')
                    ->label('Dominio')
                    ->required()
                    ->maxLength(250)
                    ->columnSpan(6),

                TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->url()
                    ->maxLength(250)
                    ->columnSpan(12),

                TextInput::make('paquete')
                    ->label('Paquete / Plan')
                    ->maxLength(250)
                    ->nullable()
                    ->columnSpan(6),

                TextInput::make('precio')
                    ->label('Precio (€)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->suffix('€')
                    ->columnSpan(6),

                DatePicker::make('fecha_alta')
                    ->label('Fecha de alta')
                    ->required()
                    ->default(fn () => now()->toDateString())
                    ->columnSpan(6),

                DatePicker::make('fecha_renovacion')
                    ->label('Fecha de renovación')
                    ->required()
                    ->default(fn () => now()->addYear()->toDateString())
                    ->columnSpan(6),

                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->columnSpan(12),

                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->nullable()
                    ->rows(3)
                    ->columnSpan(12),
            ]);
    }
}
