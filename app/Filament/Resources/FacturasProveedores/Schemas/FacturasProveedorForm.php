<?php

namespace App\Filament\Resources\FacturasProveedores\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{Select, TextInput, Textarea, DatePicker, FileUpload};
use Filament\Schemas\Components\Utilities\{Get, Set};
use Illuminate\Database\Eloquent\Builder;

class FacturasProveedorForm {

    public static function configure(Schema $schema): Schema {
        $buildSnapshot = function (?int $id): ?string {
            if (! $id) {
                return null;
            }

            $p = \App\Models\Cliente::find($id);

            return $p ? implode("\n", array_filter([
                $p->razon_social ?: $p->nombre,
                $p->nif ? "NIF: {$p->nif}" : null,
                $p->direccion,
                trim(($p->cp ? "{$p->cp} " : '') . ($p->ciudad ?: '')),
                $p->provincia ? "({$p->provincia})" : null,
                $p->pais,
            ])) : null;
        };
        $recalcTotal = function (Get $get, Set $set): void {
            $base = (float) ($get('base') ?? 0);
            $iva = (float) ($get('iva_total') ?? 0);
            $irpf = (float) ($get('irpf_total') ?? 0);

            $set('total', $base + $iva - $irpf);
        };

        return $schema
            ->columns(12)
            ->schema([
                // Proveedor (solo los marcados como proveedor = 1)
                Select::make('cliente_id')
                    ->label('Proveedor')
                    ->relationship(
                        name: 'cliente',
                        titleAttribute: 'mostrar',
                         modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) =>
                            $query->where('clientes.proveedor', 1) // columna calificada
                    )
                    ->searchable()
                    ->preload()
                    ->live(onBlur: false)
                    ->afterStateHydrated(function (Get $get, Set $set, $state) use ($buildSnapshot) {
                        if ($get('datos_proveedor')) {
                            return;
                        }

                        $set('datos_proveedor', $buildSnapshot((int) $state));
                    })
                    ->afterStateUpdated(function (Get $get, Set $set, $state) use ($buildSnapshot) {
                        $set('datos_proveedor', $buildSnapshot((int) $state));
                    })
                    ->required()
                    ->columnSpan(12),

                // Fecha de la factura del proveedor
                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->default(fn () => now()->toDateString())
                    ->required()
                    ->columnSpan(6),

                TextInput::make('numero_proveedor')
                    ->label('Número de factura del proveedor')
                    ->default('')
                    ->required()
                    ->columnSpan(6),

                // Snapshot de datos del proveedor (solo lectura, se guarda)
                Textarea::make('datos_proveedor')
                    ->label('Datos del proveedor')
                    ->rows(6)
                    ->disabled()
                    ->dehydrated(true)
                    ->columnSpan(4),
                
                // Concepto libre (opcional)
                Textarea::make('concepto')
                    ->label('Concepto')
                    ->rows(6)
                    ->required()
                    ->columnSpan(8),

                Select::make('moneda')
                    ->label("Moneda")
                    ->options([
                        "eur" => "EUR",
                        "usd" => "USD"])
                    ->default('eur')
                    ->required()
                    ->columnSpan(3),
                
                // Totales (introducidos manualmente)
                TextInput::make('base')
                    ->label('Base')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->live(onBlur: false)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $recalcTotal($get, $set))
                    ->columnSpan(3),

                TextInput::make('iva_total')
                    ->label('IVA total')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(onBlur: false)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $recalcTotal($get, $set))
                    ->columnSpan(3),

                TextInput::make('irpf_total')
                    ->label('IRPF total')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: false)
                    ->afterStateUpdated(fn (Get $get, Set $set) => $recalcTotal($get, $set))
                    ->columnSpan(3),

                TextInput::make('total')
                    ->label('Total')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->readOnly()
                    ->columnSpan(3),

                // Snapshot de datos del proveedor (solo lectura, se guarda

                // PDF adjunto de la factura
                FileUpload::make('pdf_path')
                    ->label('PDF de la factura')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('compras')
                    ->required()
                    ->columnSpan(6),
            ]);
    }
}
