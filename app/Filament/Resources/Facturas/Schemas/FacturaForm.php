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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Presupuestos\Filament\Resources\Presupuestos\PresupuestoResource;
use Presupuestos\Models\Presupuesto;

class FacturaForm
{
    public static function configure(Schema $schema): Schema
    {
        $lock        = fn (?Factura $record) => ! is_null($record?->numero);
        $lockLinea   = fn (Get $get)         => filled($get('../../numero'));  

        return $schema
            ->columns(12)
            ->schema([
                Placeholder::make('numero_factura')
                    ->label('Número de factura')
                    ->content(function (?Factura $record) {
                        if (! $record?->numero) {
                            return null;
                        }

                        $titulo = 'Factura ' . ($record->numero_completo ?? str_pad((string) $record->numero, 3, '0', STR_PAD_LEFT));
                        // HtmlString evita el escape del HTML:
                        return new HtmlString('<h2 class="text-xl font-semibold">'.$titulo.'</h2>');
                    })
                    ->visible(fn (?Factura $record) => filled($record?->numero))
                    ->columnSpanFull(),

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
                    ->columnSpan(4)
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),

                // Select self-relación (Factura -> rectifica)
                Select::make('rectifica_id')
                    ->label('Rectifica a')
                    ->searchable()
                    // resultados para el cuadro de búsqueda
                    ->getSearchResultsUsing(function (Get $get, string $search) {
                        $search = trim($search);

                        return Factura::query()
                            ->whereIn('estado', ['emitida', 'cobrada'])              
                            ->when($get('id'), fn ($q, $id) =>             
                                $q->whereKeyNot($id)
                            )
                            ->where(function ($q) use ($search) {        
                                $q->where('numero_completo', 'like', "%{$search}%");

                                if (ctype_digit($search)) {
                                    $q->orWhere('id', (int) $search);
                                }
                            })
                            ->orderByDesc('id')
                            ->limit(50)
                            ->get(['id', 'numero_completo'])
                            ->pluck('numero_completo', 'id')
                            ->toArray();
                    })
                    
                    ->getOptionLabelUsing(fn ($value) =>
                        optional(Factura::find($value))->numero_completo
                    )
                    ->live(onBlur: false)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $id = (int) ($get('rectifica_id') ?? 0);
                        if (! $id) {
                            return;
                        }

                        // 1) Cargar factura padre con sus líneas (en orden)
                        $padre = \App\Models\Factura::with(['lineas' => fn ($q) => $q->orderBy('id')])
                            ->find($id);

                        if (! $padre) {
                            return;
                        }

                        // 2) Copiar cabecera
                        $set('cliente_id',        $padre->cliente_id);
                        $set('datos_facturacion', $padre->datos_facturacion);
                        $set('moneda',            $padre->moneda ?? 'eur');

                        // 3) Preparar líneas (solo campos editables)
                        $items = [];
                        foreach ($padre->lineas as $ln) {
                            $items[] = [
                                'producto'        => (int) $ln->producto,      // 0=servicio, 1=producto
                                'concepto'        => $ln->concepto,
                                'cantidad'        => (float) $ln->cantidad,
                                'precio_unitario' => (float) $ln->precio_unitario,
                                'descuento_pct'   => (float) ($ln->descuento_pct ?? 0),
                                'impuesto_id'     => $ln->impuesto_id,
                            ];
                        }

                        // 4) Forzar rehidratación del repeater y recalcular
                        $set('lineas', []);           // <- reset para asegurar cambio de estado
                        $set('lineas', $items);

                        $lineas = $get('lineas') ?? [];
                        if (is_array($lineas)) {
                            foreach (array_keys($lineas) as $i) {
                                self::recalcularLineaEn($get, $set, (int) $i);
                            }
                        }

                        self::recalcularTotalesFactura($get, $set);
                    })
                    ->visible(fn (Get $get) => in_array($get('tipo'), ['abono','rectificativa'], true))
                    ->required(fn (Get $get) => in_array($get('tipo'), ['abono','rectificativa'], true))
                    ->columnSpan(8)
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),

                Select::make('estado')
                    ->label("Estado")
                    ->options(fn (?Factura $record) => ($record && $record->numero !== null)
                        ? [
                            'emitida' => 'Emitida',
                            'cobrada' => 'Cobrada',
                            'anulada' => 'Anulada',
                        ]
                        : [
                            'borrador' => 'Borrador',
                            'emitida'  => 'Emitida',
                            'cobrada'  => 'Cobrada',
                            'anulada'  => 'Anulada',
                        ]
                    )
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
                        self::recalcularTotalesFactura($get, $set);   
                    })
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),
                    
                Textarea::make('datos_facturacion')
                    ->rows(5)
                    ->readonly()
                    ->columnSpan(12)
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),

                DatePicker::make('fecha')
                    ->label('Fecha de emisión')
                    ->default(fn () => now()->toDateString())
                    ->minDate(fn (?Factura $record) => $lock($record) ? null : now()->toDateString())
                    ->rule(fn (?Factura $record) => $lock($record) ? null : 'after_or_equal:today')
                    ->live(onBlur: false)                        
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (!$state) {
                            $set('vencimiento', null);
                            return;
                        }
                        $base = Carbon::parse($state);
                        $set('vencimiento', $base->copy()->addWeek()->toDateString()); // +7 días
                    })
                    ->required(fn (?Factura $record) => ! $lock($record))
                    ->columnSpan(4)
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),

                DatePicker::make('vencimiento')
                    ->label('Fecha de vencimiento')
                    ->default(fn () => now()->addWeek()->toDateString())
                    ->minDate(fn (Get $get) => ($get('fecha') ?: now()->toDateString()))
                    ->required()
                    ->columnSpan(4)
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),

                Repeater::make('lineas')
                    ->label('Líneas')
                    ->relationship('lineas')      
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
                        self::recalcularTotalesFactura($get, $set);
                    }) 
                    ->afterStateUpdated(fn (Get $get, Set $set)
                        => self::recalcularTotalesFactura($get, $set)
                    )
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => blank($record?->numero))
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
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => blank($get('../../numero'))),

                        Textarea::make('concepto')
                            ->label('Concepto')
                            ->required()
                            ->rows(6)
                            ->columnSpan(6)
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => blank($get('../../numero'))),

                        TextInput::make('cantidad')
                            ->numeric()->minValue(0.10)->step('0.01')
                            ->rules(['numeric','gt:0'])
                            ->default(1)
                            ->columnSpan(2)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => blank($get('../../numero'))),

                        TextInput::make('precio_unitario')
                            ->type('number')
                            ->step('0.01')
                            ->inputMode('decimal')
                            ->label('Precio')
                            ->columnSpan(2)
                            ->required()
                            ->rules(['required', 'numeric'])  // Permite negativos
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => blank($get('../../numero'))),

                        TextInput::make('descuento_pct')
                            ->numeric()->minValue(0)->maxValue(100)->step('0.01')
                            ->label('Dto. %')
                            ->default(0)
                            ->columnSpan(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => blank($get('../../numero'))),

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
                            ->afterStateUpdated(fn ($get, $set) => self::recalcularLineaYTotales($get, $set))
                            ->disabled($lockLinea)
                            ->dehydrated(fn (Get $get) => blank($get('../../numero'))),

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
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),
                Textarea::make('notas')->rows(3)->columnSpanFull()
                    ->disabled($lock)
                    ->dehydrated(fn (?Factura $record) => is_null($record?->numero)),

                TextInput::make('hash')->label("Verifactu")->readonly()->columnSpan(6),

                Placeholder::make('presupuesto_link')
                    ->label('Presupuesto')
                    ->content(function (?Factura $record) {
                        if (! $record) {
                            return null;
                        }

                        $presupuestoId = DB::table('presupuestos_facturas')
                            ->where('factura_id', $record->id)
                            ->value('presupuesto_id');

                        if (! $presupuestoId) {
                            return new HtmlString('<div class="text-sm text-gray-500">Sin presupuesto asociado</div>');
                        }

                        $presupuesto = Presupuesto::find($presupuestoId);
                        if (! $presupuesto) {
                            return new HtmlString('<div class="text-sm text-gray-500">Presupuesto no disponible</div>');
                        }

                        $label = $presupuesto->numero_completo
                            ?? ($presupuesto->numero !== null
                                ? str_pad((string) $presupuesto->numero, 3, '0', STR_PAD_LEFT)
                                : (string) $presupuesto->id);

                        $url = PresupuestoResource::getUrl('edit', ['record' => $presupuesto]);

                        return new HtmlString(
                            '<a class="text-primary-600 hover:text-primary-700 font-semibold" href="' . e($url) . '">' . e($label) . '</a>'
                        );
                    })
                    ->visibleOn('edit')
                    ->columnSpanFull(),
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
