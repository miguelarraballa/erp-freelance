<?php

namespace App\Filament\Resources\Facturas\Schemas;

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
use Filament\Schemas\Components\Utilities\{Get, Set};
use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FacturaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([

                ToggleButtons::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'normal'        => 'Normal',
                        'abono'         => 'Abono',
                        'rectificativa' => 'Rectificativa',
                    ])
                    ->inline()
                    ->grouped()
                    ->default('normal')
                    ->live(onBlur: false)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if ($state === 'normal') {
                            $set('rectifica_id', null);
                        }
                    })
                    ->columnSpan(4),

                // Select self-relación (Factura -> rectifica)
                Select::make('rectifica_id')
                    ->label('Rectifica a')
                    ->relationship(
                        name: 'rectifica',               
                        titleAttribute: 'numero_completo', 
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $q) =>
                            $q->orderByDesc('id')
                    )
                    ->searchable()
                    ->visible(fn (Get $get) => in_array($get('tipo'), ['abono','rectificativa'], true))
                    ->required(fn (Get $get) => in_array($get('tipo'), ['abono','rectificativa'], true))
                    ->columnSpan(8),

                Select::make('estado')
                    ->label("Estado")
                    ->options([
                        "borrador"  => "Borrador",
                        "emitida"   => "Emitida",
                        "cobrada"   => "Cobrada",
                        "anulada"   => "Anulada" 
                    ])
                    ->required()
                    ->default("borrador")
                    ->columnSpan(6),

                Select::make('cliente_id')
                    ->label('Cliente')
                    ->relationship(
                        name: 'cliente',
                        titleAttribute: 'mostrar', 
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) =>
                            $query->where('clientes.cliente', 1) // columna calificada
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(12)
                    ->live(onBlur: false)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $id = is_numeric($state) ? (int) $state : (int) ($get('cliente_id') ?? 0);

                        if (!$id) {
                            $set('datos_facturacion', null);
                            return;
                        }

                        $c = Cliente::find($id);
                        $snapshot = $c ? implode("\n", array_filter([
                            $c->razon_social ?: $c->nombre,    
                            str_replace('\n',' ',$c->direccion),
                            trim(($c->cp ? "{$c->cp} " : '') . ($c->ciudad ?: '')),
                            ($c->provincia ? "{$c->provincia} "  : '') . $c->pais,
                            $c->nif ? "{$c->nif}" : null,
                        ])) : null;

                        $set('datos_facturacion', $snapshot);
                    }),
                Textarea::make('datos_facturacion')
                    ->rows(5)
                    ->readonly()
                    ->columnSpan(12),
                DatePicker::make('fecha')
                    ->label('Fecha de emisión')
                    ->default(fn () => now()->toDateString())   // hoy por defecto
                    ->live(onBlur: false)                        // dispara en el change
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (!$state) {
                            $set('vencimiento', null);
                            return;
                        }
                        $base = Carbon::parse($state);
                        $set('vencimiento', $base->copy()->addWeek()->toDateString()); // +7 días
                    })
                    ->required()
                    ->columnSpan(4),
                DatePicker::make('vencimiento')
                    ->label('Fecha de vencimiento')
                    ->default(fn () => now()->addWeek()->toDateString()) // una semana desde hoy
                    ->required()
                    ->columnSpan(4),

                Repeater::make('lineas')
                    ->label('Líneas')
                    ->relationship('lineas')      // hasMany Factura -> FacturaLinea
                    ->defaultItems(1)
                    ->minItems(1)
                    ->columnSpanFull()
                    ->columns(12)                 // grid interno del repeater
                    ->schema([

                        ToggleButtons::make('producto')
                            ->options([0 => 'Servicio', 1 => 'Producto'])
                            ->label('Tipo')
                            ->inline()          
                            ->grouped()        
                            ->required()
                            ->default(0)
                            ->columnSpanFull(),

                        Textarea::make('concepto')
                            ->label('Concepto')
                            ->required()
                            ->rows(6)
                            ->columnSpan(6),

                        TextInput::make('cantidad')
                            ->numeric()->minValue(0.001)->step('0.001')
                            ->default(1)
                            ->columnSpan(2),

                        TextInput::make('precio_unitario')
                            ->numeric()->minValue(0)->step('0.01')
                            ->label('Precio')
                            ->columnSpan(2),

                        TextInput::make('descuento_pct')
                            ->numeric()->minValue(0)->maxValue(100)->step('0.01')
                            ->label('Dto. %')
                            ->default(0)
                            ->columnSpan(2),

                        Select::make('impuesto_id')
                            ->label('Impuesto')
                            ->relationship('impuesto', 'nombre') // pertenece a FacturaLinea -> Impuesto
                            ->searchable()
                            ->preload()
                            ->default('IVA 21')
                            ->columnSpan(4),

                        // (Opcional) campos calculados persistidos; se recomiendan sólo lectura:
                        TextInput::make('base_linea')->readOnly()->numeric()->columnSpan(2),
                        TextInput::make('iva_linea')->readOnly()->numeric()->columnSpan(2),
                        TextInput::make('irpf_linea')->readOnly()->numeric()->columnSpan(2),
                        TextInput::make('total_linea')->readOnly()->numeric()->columnSpan(2),
                    ]),
                
                TextInput::make('base')->label("Base")->readOnly()->numeric()->columnSpan(3),
                TextInput::make('iva_total')->label("IVA Total")->readOnly()->numeric()->columnSpan(3),
                TextInput::make('irpf_total')->label("IRPF Total")->readOnly()->numeric()->columnSpan(3),
                TextInput::make('total')->label("Total")->readOnly()->numeric()->columnSpan(3),
                Select::make('moneda')
                    ->label("Moneda")
                    ->options([
                        "eur" => "EUR",
                        "usd" => "USD"])
                    ->default('eur')
                    ->required()
                    ->columnSpan(3),
                Textarea::make('notas')->rows(3)->columnSpanFull(),
                TextInput::make('hash')->label("Verifactu")->readonly()->columnSpan(6),
            ]);

    }
}
