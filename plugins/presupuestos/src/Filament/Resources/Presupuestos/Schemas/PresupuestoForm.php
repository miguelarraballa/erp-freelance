<?php

namespace Presupuestos\Filament\Resources\Presupuestos\Schemas;

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
    Hidden,
};
use Filament\Schemas\Components\Utilities\{Get, Set};
use App\Filament\Resources\Facturas\FacturaResource;
use App\Models\{Impuesto, Cliente, Factura};
use Presupuestos\Models\Presupuesto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class PresupuestoForm
{
    public static function configure(Schema $schema): Schema
    {
        $tieneFactura = fn (?Presupuesto $record) => $record?->exists
            && DB::table('presupuestos_facturas')
                ->where('presupuesto_id', $record->id)
                ->exists();
        $editable = fn (?Presupuesto $record) => blank($record) || $record->estado === 'borrador';
        $lock = fn (?Presupuesto $record) => ! $editable($record);
        $lockEstado = fn (?Presupuesto $record) => $record?->estado === 'facturado' && $tieneFactura($record);
        $lockLinea = fn (Get $get) => $get('../../estado') !== 'borrador';

        return $schema
            ->columns(12)
            ->schema([
                Placeholder::make('numero_presupuesto')
                    ->label('Número de presupuesto')
                    ->content(function (?Presupuesto $record) {
                        if (! $record?->numero) {
                            return null;
                        }

                        $titulo = 'Presupuesto ' . ($record->numero_completo ?? str_pad((string) $record->numero, 3, '0', STR_PAD_LEFT));
                        // HtmlString evita el escape del HTML:
                        return new HtmlString('<h2 class="text-xl font-semibold">'.$titulo.'</h2>');
                    })
                    ->visible(fn (?Presupuesto $record) => filled($record?->numero))
                    ->columnSpanFull(),

                Select::make('estado')
                    ->label("Estado")
                    ->options(function (?Presupuesto $record) {
                        if ($record?->estado === 'facturado') {
                            return [
                                'emitido' => 'Emitido',
                                'facturado' => 'Facturado',
                            ];
                        }

                        if ($record?->estado === 'aceptado') {
                            return [
                                'emitido' => 'Emitido',
                                'aceptado' => 'Aceptado',
                                'facturado' => 'Facturado',
                            ];
                        }

                        return ($record && $record->numero !== null)
                            ? [
                                'borrador'      => 'Borrador',
                                'emitido'       => 'Emitido',
                                'aceptado'      => 'Aceptado',
                                'no-aceptado'   => 'No Aceptado',
                                'facturado'     => 'Facturado',

                            ]
                            : [
                                'borrador'  => 'Borrador',
                                'emitido'   => 'Emitido',
                            ];
                    })
                    ->disabled($lockEstado)
                    ->dehydrated(fn (?Presupuesto $record) => ! $lockEstado($record))
                    ->required()
                    ->default("borrador")
                    ->hiddenOn('create')
                    ->visibleOn('edit') 
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
                        self::recalcularTotalesPresupuesto($get, $set);   
                    })
                    ->disabled($lock)
                    ->dehydrated($editable),
                    
                Textarea::make('datos_facturacion')
                    ->rows(5)
                    ->readonly()
                    ->columnSpan(12)
                    ->disabled($lock)
                    ->dehydrated($editable),

                DatePicker::make('fecha')
                    ->label('Fecha de emisión')
                    ->default(fn () => now()->toDateString())
                    ->minDate(fn (?Presupuesto $record) => (filled($record?->numero) || $lock($record)) ? null : now()->toDateString())
                    ->rule(fn (?Presupuesto $record) => (filled($record?->numero) || $lock($record)) ? null : 'after_or_equal:today')
                    ->live(onBlur: false)                        
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (!$state) {
                            $set('vencimiento', null);
                            return;
                        }
                        $base = Carbon::parse($state);
                        $set('vencimiento', $base->copy()->addMonth(3)->toDateString()); // 3 meses por defecto
                    })
                    ->required(fn (?Presupuesto $record) => ! $lock($record))
                    ->columnSpan(4)
                    ->disabled($lock)
                    ->dehydrated($editable),

                DatePicker::make('vencimiento')
                    ->label('Fecha de vencimiento')
                    ->default(fn () => now()->addMonth(3)->toDateString())
                    ->minDate(fn (Get $get) => ($get('fecha') ?: now()->toDateString()))
                    ->required()
                    ->columnSpan(4)
                    ->disabled($lock)
                    ->dehydrated($editable),

                Repeater::make('lineas')
                    ->label('Líneas')
                    ->relationship('lineas')
                    ->reorderable('orden')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->rules(['required','array','min:1'])
                    ->columnSpanFull()
                    ->columns(12)
                    ->afterStateHydrated(function (Get $get, Set $set, $state) {
                        $lineas = $get('lineas') ?? [];
                        if (is_array($lineas)) {
                            foreach (array_keys($lineas) as $i) {
                                self::recalcularLineaEn($get, $set, $i);
                            }
                        }
                        self::recalcularTotalesPresupuesto($get, $set);
                    }) 
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $lineas = $get('lineas') ?? [];
                        $pos = 1;
                        foreach (array_keys($lineas) as $key) {
                            $set("lineas.$key.orden", $pos++);
                        }
                        self::recalcularTotalesPresupuesto($get, $set);
                    })
                    ->disabled($lock)
                    ->dehydrated($editable)
                    ->schema([

                        Hidden::make('orden'),

                        ToggleButtons::make('producto')
                            ->options([0 => 'Servicio', 1 => 'Producto'])
                            ->label('Tipo')
                            ->inline()          
                            ->grouped()        
                            ->required()
                            ->default(0)
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => $get('../../estado') === 'borrador'),

                        Textarea::make('concepto')
                            ->label('Concepto')
                            ->required()
                            ->rows(6)
                            ->columnSpan(6)
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => $get('../../estado') === 'borrador'),

                        TextInput::make('cantidad')
                            ->numeric()->minValue(0.1)->step('0.1')
                            ->rules(['numeric','gt:0'])
                            ->default(1)
                            ->columnSpan(2)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => $get('../../estado') === 'borrador'),

                        TextInput::make('precio_unitario')
                            ->type('number')
                            ->step('0.01')
                            ->inputMode('decimal')
                            ->label('Precio')
                            ->columnSpan(2)
                            ->required()
                            ->rules(['required', 'numeric'])  // Permite negativos
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => $get('../../estado') === 'borrador'),

                        TextInput::make('descuento_pct')
                            ->numeric()->minValue(0)->maxValue(100)->step('0.01')
                            ->label('Dto. %')
                            ->default(0)
                            ->columnSpan(2)
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => $get('../../estado') === 'borrador'),

                        Select::make('impuesto_id')
                            ->label('Impuesto')
                            ->relationship('impuesto', 'nombre') // pertenece a ProductoLinea -> Impuesto
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
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => $get('../../estado') === 'borrador'),

                        // (Opcional) campos calculados persistidos; se recomiendan sólo lectura:
                        TextInput::make('base_linea')
                            ->readOnly()
                            ->type('number')
                            ->step('any')
                            ->extraInputAttributes(['step' => 'any'])
                            ->rules(['nullable', 'numeric'])
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                            ->columnSpan(2),
                        TextInput::make('iva_linea')
                            ->readOnly()
                            ->type('number')
                            ->step('any')
                            ->extraInputAttributes(['step' => 'any'])
                            ->rules(['nullable', 'numeric'])
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                            ->columnSpan(2),
                        TextInput::make('irpf_linea')
                            ->readOnly()
                            ->type('number')
                            ->step('any')
                            ->extraInputAttributes(['step' => 'any'])
                            ->rules(['nullable', 'numeric'])
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                            ->columnSpan(2),
                        TextInput::make('total_linea')
                            ->readOnly()
                            ->type('number')
                            ->step('any')
                            ->extraInputAttributes(['step' => 'any'])
                            ->rules(['nullable', 'numeric'])
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                            ->columnSpan(2),
                    ]),
                
                TextInput::make('base')
                    ->label("Base")
                    ->readOnly()
                    ->type('number')
                    ->step('any')
                    ->extraInputAttributes(['step' => 'any'])
                    ->rules(['nullable', 'numeric'])
                    ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                    ->columnSpan(3),

                TextInput::make('iva_total')
                    ->label("IVA Total")
                    ->readOnly()
                    ->type('number')
                    ->step('any')
                    ->extraInputAttributes(['step' => 'any'])
                    ->rules(['nullable', 'numeric'])
                    ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                    ->columnSpan(3),

                TextInput::make('irpf_total')
                    ->label("IRPF Total")
                    ->readOnly()
                    ->type('number')
                    ->step('any')
                    ->extraInputAttributes(['step' => 'any'])
                    ->rules(['nullable', 'numeric'])
                    ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                    ->columnSpan(3),

                TextInput::make('total')
                    ->label("Total")
                    ->readOnly()
                    ->type('number')
                    ->step('any')
                    ->extraInputAttributes(['step' => 'any'])
                    ->rules(['nullable', 'numeric'])
                    ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) $state : null)
                    ->columnSpan(3),

                Select::make('moneda')
                    ->label("Moneda")
                    ->options([
                        "eur" => "EUR",
                        "usd" => "USD"])
                    ->default('eur')
                    ->required()
                    ->columnSpan(3)
                    ->disabled($lock)
                    ->dehydrated($editable),
                Textarea::make('notas')->rows(3)->columnSpanFull()
                    ->disabled($lock)
                    ->dehydrated($editable),

                Placeholder::make('factura_link')
                    ->label('Facturas')
                    ->content(function (?Presupuesto $record) {
                        if (! $record) {
                            return null;
                        }

                        $facturas = Factura::query()
                            ->whereIn('id', DB::table('presupuestos_facturas')
                                ->where('presupuesto_id', $record->id)
                                ->select('factura_id'))
                            ->orderByDesc('id')
                            ->get();

                        if ($facturas->isEmpty()) {
                            return new HtmlString('<div class="text-sm text-gray-500">Sin facturas asociadas</div>');
                        }

                        $links = $facturas->map(function (Factura $factura): string {
                            $label = $factura->numero_completo ? $factura->numero_completo
                                : "Provisional #" . str_pad((string) $factura->id, 5, '0', STR_PAD_LEFT);

                            $url = FacturaResource::getUrl('edit', ['record' => $factura]);

                            return '<li><a class="text-primary-600 hover:text-primary-700 font-semibold" href="' . e($url) . '">' . e($label) . '</a></li>';
                        })->implode('');

                        return new HtmlString('<ul class="space-y-1">' . $links . '</ul>');
                    })
                    ->visibleOn('edit')
                    ->columnSpanFull(),
 
            ]);
    }

    private static function recalcularLineaYTotales(Get $get, Set $set): void
    {
        self::recalcularLineaActual($get, $set);
        self::recalcularTotalesPresupuesto($get, $set);
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

        // Normalizar -0 a 0 para evitar CorruptComponentPayloadException de Livewire
        $base  = round($bruto, 2);
        $base  = $base == 0 ? 0.0 : $base;

        $iva   = round($base * ($ivaPct / 100), 2);
        $iva   = $iva == 0 ? 0.0 : $iva;

        $irpf  = round($base * ($irpfPct / 100), 2);
        $irpf  = $irpf == 0 ? 0.0 : $irpf;

        $total = round($base + $iva - $irpf, 2);
        $total = $total == 0 ? 0.0 : $total;

        // Set línea actual
        $set('base_linea',  $base);
        $set('iva_linea',   $iva);
        $set('irpf_linea',  $irpf);
        $set('total_linea', $total);
    }

    private static function recalcularTotalesPresupuesto(Get $get, Set $set): void
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

        // Normalizar -0 a 0 para evitar CorruptComponentPayloadException de Livewire
        $base = round($sumBase, 2);
        $base = $base == 0 ? 0.0 : $base;

        $ivaTotal = round($sumIva, 2);
        $ivaTotal = $ivaTotal == 0 ? 0.0 : $ivaTotal;

        $irpfTotal = round($sumIrpf, 2);
        $irpfTotal = $irpfTotal == 0 ? 0.0 : $irpfTotal;

        $total = round($sumTot, 2);
        $total = $total == 0 ? 0.0 : $total;

        $set('base',       $base);
        $set('iva_total',  $ivaTotal);
        $set('irpf_total', $irpfTotal);
        $set('total',      $total);
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

        // Normalizar -0 a 0 para evitar CorruptComponentPayloadException de Livewire
        $base  = round($bruto, 2);
        $base  = $base == 0 ? 0.0 : $base;

        $iva   = round($base * ($ivaPct / 100), 2);
        $iva   = $iva == 0 ? 0.0 : $iva;

        $irpf  = round($base * ($irpfPct / 100), 2);
        $irpf  = $irpf == 0 ? 0.0 : $irpf;

        $total = round($base + $iva - $irpf, 2);
        $total = $total == 0 ? 0.0 : $total;

        $set("lineas.$i.base_linea",  $base);
        $set("lineas.$i.iva_linea",   $iva);
        $set("lineas.$i.irpf_linea",  $irpf);
        $set("lineas.$i.total_linea", $total);
    }
}
