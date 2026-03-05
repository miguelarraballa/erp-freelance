<?php

namespace Woocommerce\Filament\Resources\TiendasWoo\Schemas;

use App\Models\Cliente;
use App\Models\Serie;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TiendaWooForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre de la tienda')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan(6),

                TextInput::make('url')
                    ->label('URL de la tienda')
                    ->url()
                    ->required()
                    ->placeholder('https://mitienda.com')
                    ->helperText('URL base de tu tienda WooCommerce (sin /wp-json)')
                    ->columnSpan(6),

                TextInput::make('consumer_key')
                    ->label('Consumer Key')
                    ->required()
                    ->password()
                    ->revealable()
                    ->placeholder('ck_...')
                    ->columnSpan(6),

                TextInput::make('consumer_secret')
                    ->label('Consumer Secret')
                    ->required()
                    ->password()
                    ->revealable()
                    ->placeholder('cs_...')
                    ->columnSpan(6),

                Select::make('serie_id')
                    ->label('Serie de facturas simplificadas')
                    ->options(
                        Serie::where('tipo', 'simplificada')
                            ->where('activo', true)
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => "{$s->prefijo}{$s->codigo}{$s->sufijo} ({$s->ejercicio})"])
                    )
                    ->searchable()
                    ->required()
                    ->helperText('Crea primero una serie de tipo "Simplificada (WooCommerce)" en Configuración → Series')
                    ->columnSpan(6),

                Select::make('cliente_id')
                    ->label('Cliente genérico de la tienda')
                    ->options(
                        Cliente::where('cliente', 1)
                            ->orderBy('razon_social')
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->mostrar])
                    )
                    ->searchable()
                    ->required()
                    ->helperText('Cliente al que se asignarán todas las facturas de esta tienda (ej: "Clientes Tienda Online")')
                    ->columnSpan(6),

                Toggle::make('activo')
                    ->label('Activa')
                    ->default(true)
                    ->columnSpan(12),
            ]);
    }
}
