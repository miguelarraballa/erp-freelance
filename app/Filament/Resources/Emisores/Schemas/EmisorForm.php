<?php

namespace App\Filament\Resources\Emisores\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{
    TextInput, Textarea, Toggle, FileUpload, Select
};
use Filament\Schemas\Components\Utilities\{Get, Set};
use App\Rules\SpanishDocIdRule;
use App\Forms\Components\CountrySelect; // tu componente de países (si lo tienes)

class EmisorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre fiscal')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(8),

                Toggle::make('activo')
                    ->label('Activo')
                    ->inline(false)
                    ->helperText('Debe existir solo un emisor activo.')
                    ->columnSpan(4),

                TextInput::make('nif')
                    ->label('NIF/CIF/NIE')
                    ->rules([new SpanishDocIdRule()])
                    ->columnSpan(4),

                TextInput::make('direccion')->columnSpan(8),

                TextInput::make('cp')->maxLength(10)->columnSpan(2),
                TextInput::make('ciudad')->columnSpan(5),
                TextInput::make('provincia')->columnSpan(5),

                // Usa tu CountrySelect si lo tienes, si no, un Select normal
                (class_exists(CountrySelect::class)
                    ? CountrySelect::make('pais')->label('País')->columnSpan(4)
                    : Select::make('pais')
                        ->label('País')
                        ->options(['ES' => 'España']) // completa si no usas CountrySelect
                        ->searchable()
                        ->columnSpan(4)
                ),

                TextInput::make('email')->email()->columnSpan(6),
                TextInput::make('telefono')->columnSpan(6),
                TextInput::make('web')->url()->columnSpan(12),

                TextInput::make('iban')->maxLength(34)->columnSpan(8),
                TextInput::make('swift_bic')->maxLength(11)->columnSpan(4),

                FileUpload::make('logo_path')
                    ->label('Logo')
                    ->image()
                    ->directory('emisores')
                    ->preserveFilenames()
                    ->columnSpan(6),

                Textarea::make('pie_factura')
                    ->label('Pie de factura')
                    ->rows(4)
                    ->columnSpan(6),

                Textarea::make('notas_legales')
                    ->label('Notas legales')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }
}