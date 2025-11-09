<?php

namespace App\Filament\Resources\FacturasProveedores\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{Select, TextInput, Textarea, DatePicker, FileUpload};
use Filament\Schemas\Components\Utilities\{Get, Set};
use Illuminate\Database\Eloquent\Builder;

class FacturasProveedorForm {

    public static function configure(Schema $schema): Schema {
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
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $id = $get('cliente_id');
                        if (!$id) { $set('datos_proveedor', null); return; }

                        $p = \App\Models\Cliente::find($id);
                        $snapshot = $p ? implode("\n", array_filter([
                            $p->razon_social ?: $p->nombre,
                            $p->nif ? "NIF: {$p->nif}" : null,
                            $p->direccion,
                            trim(($p->cp ? "{$p->cp} " : '') . ($p->ciudad ?: '')),
                            $p->provincia ? "({$p->provincia})" : null,
                            $p->pais,
                        ])) : null;

                        $set('datos_proveedor', $snapshot);
                    })
                    ->required()
                    ->columnSpan(6),

                // Serie (usa tu tabla 'series')
                Select::make('serie_id')
                    ->label('Serie')
                    ->relationship('serie', 'codigo')
                    ->searchable()
                    ->required()
                    ->columnSpan(3),

                // Nº del proveedor
                TextInput::make('numero_proveedor')
                    ->label('Nº proveedor')
                    ->maxLength(100)
                    ->required()
                    ->columnSpan(3),

                // Fecha de la factura del proveedor
                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->default(fn () => now()->toDateString())
                    ->required()
                    ->columnSpan(3),

                // Concepto libre (opcional)
                Textarea::make('concepto')
                    ->label('Concepto')
                    ->rows(3)
                    ->columnSpan(9),

                // Totales (introducidos manualmente)
                TextInput::make('base')
                    ->label('Base')
                    ->numeric()->minValue(0)->step('0.01')
                    ->required()
                    ->columnSpan(3),

                TextInput::make('iva_total')
                    ->label('IVA total')
                    ->numeric()->minValue(0)->step('0.01')
                    ->required()
                    ->columnSpan(3),

                TextInput::make('irpf_total')
                    ->label('IRPF total')
                    ->numeric()->minValue(0)->step('0.01')
                    ->default(0)
                    ->columnSpan(3),

                TextInput::make('total')
                    ->label('Total')
                    ->numeric()->minValue(0)->step('0.01')
                    ->required()
                    ->columnSpan(3),

                // Snapshot de datos del proveedor (solo lectura, se guarda)
                Textarea::make('datos_proveedor')
                    ->label('Datos del proveedor')
                    ->rows(5)
                    ->disabled()
                    ->dehydrated(true)
                    ->required()
                    ->columnSpan(6),

                // PDF adjunto (opcional)
                FileUpload::make('pdf_path')
                    ->label('PDF de la factura')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('compras') // ajusta si quieres otra carpeta
                    ->columnSpan(6),
            ]);
    }
}
