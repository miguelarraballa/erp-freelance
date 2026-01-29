<?php

namespace Proyectos\Filament\Resources\Proyectos\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\{BulkAction, BulkActionGroup, CreateAction, DeleteAction, DeleteBulkAction, EditAction};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\{Factura, FacturaLinea, Cliente, Impuesto};
use App\Services\FacturaCalc;
use Proyectos\Filament\Resources\ProyectoTareas\Schemas\ProyectoTareaForm;
use Proyectos\Models\ProyectoTarea;

class ProyectoTareasRelationManager extends RelationManager
{
    protected static string $relationship = 'tareas';

    public function form(Schema $schema): Schema
    {
        return ProyectoTareaForm::configure($schema, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->wrap(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('inicio')
                    ->label('Inicio')
                    ->formatStateUsing(fn ($state) => $state ? substr((string) $state, 0, 5) : null),
                TextColumn::make('fin')
                    ->label('Fin')
                    ->formatStateUsing(fn ($state) => $state ? substr((string) $state, 0, 5) : null),
                TextColumn::make('duracion')
                    ->label('Duración')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                IconColumn::make('facturado')
                    ->label('Facturado')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Crear tarea')
                    ->disabled(fn () => (bool) $this->getOwnerRecord()?->cerrado)
                    ->hidden(fn () => (bool) $this->getOwnerRecord()?->cerrado),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (ProyectoTarea $record) => (bool) $record->facturado)
                    ->hidden(fn (ProyectoTarea $record) => (bool) $record->facturado),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('facturar')
                        ->label('Facturar tareas')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $this->facturarTareas($records)),
                    DeleteBulkAction::make()
                        ->disabled(fn (Collection $records) => $records->where('facturado', true)->count() > 0),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (ProyectoTarea $record) => ! $record->facturado
            );
    }

    protected function facturarTareas(Collection $records): void
    {
        $proyecto = $this->getOwnerRecord();
        $proyecto->loadMissing('cliente');

        $tareas = $records
            ->filter(fn (ProyectoTarea $tarea) => ! $tarea->facturado)
            ->values();

        if ($tareas->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($proyecto, $tareas) {
            $cliente = $proyecto->cliente;
            if (! $cliente) {
                throw new \RuntimeException('El proyecto no tiene cliente asociado.');
            }

            $factura = Factura::create([
                'cliente_id' => $cliente->id,
                'datos_facturacion' => $this->snapshotDatosFacturacion($cliente),
                'fecha' => now()->toDateString(),
                'vencimiento' => now()->addWeek()->toDateString(),
                'estado' => 'borrador',
                'tipo' => 'normal',
                'moneda' => 'EUR',
            ]);

            $impuestoIva = Impuesto::query()
                ->where('tipo', 'iva')
                ->where('porcentaje', 21)
                ->where('activo', true)
                ->first();

            $orden = 1;
            foreach ($tareas as $tarea) {
                FacturaLinea::create([
                    'factura_id' => $factura->id,
                    'orden' => $orden++,
                    'producto' => 0,
                    'concepto' => $tarea->descripcion . "\n" . $tarea->fecha->format('d-m-Y'),
                    'cantidad' => (float) ($tarea->duracion ?? 1),
                    'precio_unitario' => (float) ($tarea->precio ?? 0),
                    'descuento_pct' => 0,
                    'impuesto_id' => $impuestoIva?->id,
                ]);
            }

            FacturaCalc::recalcular($factura->load('lineas.impuesto'));

            ProyectoTarea::whereKey($tareas->pluck('id'))
                ->update(['facturado' => true]);
        });
    }

    protected function snapshotDatosFacturacion(Cliente $cliente): ?string
    {
        return implode("\n", array_filter([
            $cliente->razon_social ?: $cliente->nombre,
            str_replace('\n', ' ', (string) $cliente->direccion),
            trim(($cliente->cp ? "{$cliente->cp} " : '') . ($cliente->ciudad ?: '')),
            ($cliente->provincia ? "{$cliente->provincia} " : '') . ($cliente->pais ?: ''),
            $cliente->nif ?: null,
        ]));
    }
}
