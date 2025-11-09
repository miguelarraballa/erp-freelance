<?php

namespace App\Filament\Resources\Pagos\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{
    TextInput,
    DatePicker,
    Select,
};
use Illuminate\Support\Carbon;


class PagoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                 Select::make('factura_id')
                    ->label('Factura')
                    ->relationship(
                        name: 'factura',               
                        titleAttribute: 'numero_completo', 
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $q) =>
                            $q->where('estado','<>','cobrada')->orderByDesc('id')
                    )
                    ->searchable()
                    ->required()
                    ->columnSpan(5),
                DatePicker::make('fecha_pago')
                    ->label('Fecha del pago')
                    ->required()
                    ->default(fn () => now()->toDateString())
                    ->columnSpan(5),
                TextInput::make('importe')
                    ->required()
                    ->numeric()
                    ->columnSpan(2),
            ]);
    }
}
