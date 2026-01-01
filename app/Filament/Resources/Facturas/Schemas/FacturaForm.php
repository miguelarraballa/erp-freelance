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
use App\Models\{Impuesto, Cliente, Factura};
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
                    })
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $lineas = $get('lineas') ?? [];
                        if (is_array($lineas)) {
                            foreach (array_keys($lineas) as $i) {
                                self::recalcularLineaEn($get, $set, $i); 
                            }
                        }
                        self::recalcularTotalesFactura($get, $set);   
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
                    ->relationship('lineas')      
                    ->defaultItems(1)
                    ->minItems(1)
                    ->columnSpanFull()
                    ->columns(12)
                    ->afterStateHydrated(function (Get $get, Set $set, $state) {
                        $lineas = $get('lineas') ?? [];
                        if (is_array($lineas)) {
                            foreach (array_keys($lineas) as $i) {
                                self::recalcularLineaEn($get, $set, $i);
                            }
                        }
                        self::recalcularTotalesFactura($get, $set);
                    }) 
                    ->afterStateUpdated(fn (Get $get, Set $set)
                        => self::recalcularTotalesFactura($get, $set)
                    )
                    ->schema([

                        ToggleButtons::make('producto')
                            ->options([0 => 'Servicio', 1 => 'Producto'])
                            ->label('Tipo')
                            ->inline()          
                            ->grouped()        
                            ->required()
                            ->default(0)
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set)),

                        Textarea::make('concepto')
                            ->label('Concepto')
                            ->required()
                            ->rows(6)
                            ->columnSpan(6),

                        TextInput::make('cantidad')
                            ->numeric()->minValue(1)->step('1')
                            ->default(1)
                            ->columnSpan(2)
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set)),

                        TextInput::make('precio_unitario')
                            ->numeric()->minValue(0)->step('0.01')
                            ->label('Precio')
                            ->columnSpan(2)
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set)),

                        TextInput::make('descuento_pct')
                            ->numeric()->minValue(0)->maxValue(100)->step('0.01')
                            ->label('Dto. %')
                            ->default(0)
                            ->columnSpan(2)
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set)),

                        Select::make('impuesto_id')
                            ->label('Impuesto')
                            ->relationship('impuesto', 'nombre') // pertenece a FacturaLinea -> Impuesto
                            ->searchable()
                            ->preload()
                            ->default(fn () => \App\Models\Impuesto::query()
                                ->where('tipo', 'iva')
                                ->where('activo', 1)
                                ->orderByDesc('porcentaje')
                                ->value('id') ?? null
                            )
                            ->columnSpan(4)
                            ->live()
                            ->afterStateHydrated(fn (Get $get, Set $set) => self::recalcularLineaYTotales($get, $set))
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set)),

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

    private static function recalcularLineaYTotales(Get $get, Set $set): void
    {
        self::recalcularLineaActual($get, $set);
        self::recalcularTotalesFactura($get, $set);
    }

    private static function recalcularLineaActual(Get $get, Set $set): void
    {
        $cantidad = (float) ($get('cantidad') ?? 0);
        $precio   = (float) ($get('precio_unitario') ?? 0);
        $dtoPct   = max(0.0, min(100.0, (float) ($get('descuento_pct') ?? 0)));
        $producto = (int)   ($get('producto') ?? 0); // 0=Servicio, 1=Producto
        $impuestoId = (int) ($get('impuesto_id') ?? 0);

        // IVA %
        $ivaPct = 0.0;
        if ($impuestoId > 0) {
            $ivaPct = (float) (Impuesto::query()->whereKey($impuestoId)->value('porcentaje') ?? 0);
        }

        // ¿Cliente sujeto a IRPF?
        $clienteId = $get('cliente_id') ?? $get('../../cliente_id'); // según el scope
        $aplicaIrpf = false;
        if ($clienteId) {
            $aplicaIrpf = (bool) (Cliente::query()->whereKey($clienteId)->value('irpf') ?? false);
        }

        // IRPF % (si aplica y la línea es servicio)
        $irpfPct = 0.0;
        if ($aplicaIrpf && $producto === 0) {
            $irpfPct = (float) (Impuesto::query()
                ->where('tipo', 'irpf')
                ->where('activo', 1)
                ->orderByDesc('porcentaje')
                ->value('porcentaje') ?? 0);
        }

        // Cálculos
        $bruto = $cantidad * $precio;
        $bruto = $bruto * (1 - ($dtoPct / 100));

        $base  = round($bruto, 2);
        $iva   = round($base * ($ivaPct / 100), 2);
        $irpf  = round($base * ($irpfPct / 100), 2);
        $total = round($base + $iva - $irpf, 2);

        // Set línea actual
        $set('base_linea',  $base);
        $set('iva_linea',   $iva);
        $set('irpf_linea',  $irpf);
        $set('total_linea', $total);
    }

    private static function recalcularTotalesFactura(Get $get, Set $set): void
    {
        // Asegura que 'lineas' es un array
        $lineas = $get('lineas') ?? [];
        if (!is_array($lineas)) {
            return;
        }

        // 1) Recalcula todas las líneas por índice (por si alguna no está actualizada)
        foreach (array_keys($lineas) as $i) {
            self::recalcularLineaEn($get, $set, $i);
        }

        // 2) Relee el estado ya actualizado y suma
        $lineas = $get('lineas') ?? [];
        $sumBase = $sumIva = $sumIrpf = $sumTot = 0.0;

        foreach ($lineas as $ln) {
            $sumBase += (float) ($ln['base_linea']  ?? 0);
            $sumIva  += (float) ($ln['iva_linea']   ?? 0);
            $sumIrpf += (float) ($ln['irpf_linea']  ?? 0);
            $sumTot  += (float) ($ln['total_linea'] ?? 0);
        }

        $set('base',       round($sumBase, 2));
        $set('iva_total',  round($sumIva,  2));
        $set('irpf_total', round($sumIrpf, 2));
        $set('total',      round($sumTot,  2));
    }


    private static function recalcularLineaEn(Get $get, Set $set, int|string $i): void
    {
        $cantidad   = (float) ($get("lineas.$i.cantidad") ?? 0);
        $precio     = (float) ($get("lineas.$i.precio_unitario") ?? 0);
        $dtoPct     = max(0.0, min(100.0, (float) ($get("lineas.$i.descuento_pct") ?? 0)));
        $producto   = (int)   ($get("lineas.$i.producto") ?? 0);
        $impuestoId = (int)   ($get("lineas.$i.impuesto_id") ?? 0);

        $ivaPct = $impuestoId > 0
            ? (float) (Impuesto::query()->whereKey($impuestoId)->value('porcentaje') ?? 0)
            : 0.0;

        $clienteId  = $get('cliente_id') ?? null;
        $aplicaIrpf = $clienteId ? (bool) (Cliente::query()->whereKey($clienteId)->value('irpf') ?? false) : false;

        $irpfPct = ($aplicaIrpf && $producto === 0)
            ? (float) (Impuesto::query()->where('tipo', 'irpf')->where('activo', 1)->orderByDesc('porcentaje')->value('porcentaje') ?? 0)
            : 0.0;

        $bruto = $cantidad * $precio;
        $bruto = $bruto * (1 - ($dtoPct / 100));

        $base  = round($bruto, 2);
        $iva   = round($base * ($ivaPct / 100), 2);
        $irpf  = round($base * ($irpfPct / 100), 2);
        $total = round($base + $iva - $irpf, 2);

        $set("lineas.$i.base_linea",  $base);
        $set("lineas.$i.iva_linea",   $iva);
        $set("lineas.$i.irpf_linea",  $irpf);
        $set("lineas.$i.total_linea", $total);
    }
}
