<?php

namespace Proyectos\Filament\Resources\Proyectos\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{TextInput, Textarea, Toggle, DatePicker, Select};
use Filament\Schemas\Components\Utilities\Get;
use Proyectos\Models\Proyecto;

class ProyectoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                Select::make('cliente_id')
                    ->label('Cliente')
                    ->relationship(
                        name: 'cliente',
                        titleAttribute: 'mostrar',
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) =>
                            $query->where('clientes.cliente', 1)
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(12)
                    ->disabled(fn (?Proyecto $record) => (bool) $record?->cerrado),

                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(8)
                    ->disabled(fn (?Proyecto $record) => (bool) $record?->cerrado),

                Toggle::make('cerrado')
                    ->label('Cerrado')
                    ->default(false)
                    ->columnSpan(4),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(6)
                    ->columnSpan(12)
                    ->disabled(fn (?Proyecto $record) => (bool) $record?->cerrado),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha de inicio')
                    ->default(fn () => now()->toDateString())
                    ->required()
                    ->live()
                    ->columnSpan(6)
                    ->disabled(fn (?Proyecto $record) => (bool) $record?->cerrado),

                DatePicker::make('fecha_fin')
                    ->label('Fecha de fin')
                    ->minDate(fn (Get $get) => $get('fecha_inicio') ?: null)
                    ->rule(fn (Get $get) => $get('fecha_inicio') ? 'after_or_equal:fecha_inicio' : null)
                    ->columnSpan(6)
                    ->disabled(fn (?Proyecto $record) => (bool) $record?->cerrado),
            ]);
    }
}
