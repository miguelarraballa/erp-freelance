<?php

namespace Proyectos\Filament\Resources\Proyectos\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\{Action, BulkAction, BulkActionGroup, CreateAction, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\{Factura, FacturaLinea, Cliente, Impuesto};
use App\Services\FacturaCalc;
use Proyectos\Filament\Resources\ProyectoTareas\Schemas\ProyectoTareaForm;
use Proyectos\Models\ProyectoTarea;
use Carbon\Carbon;

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
            ->description(fn () => view('filament.proyectos.tareas-stats', [
                'stats' => $this->getTareasStats(),
            ]))
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
                Action::make('importar_clockify')
                    ->label('Importar Clockify')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->disabled(fn () => (bool) $this->getOwnerRecord()?->cerrado)
                    ->hidden(fn () => (bool) $this->getOwnerRecord()?->cerrado)
                    ->form([
                        FileUpload::make('csv')
                            ->label('Informe CSV de Clockify')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.ms-excel',
                            ])
                            ->disk('local')
                            ->directory('clockify-imports')
                            ->visibility('private')
                            ->maxFiles(1)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $proyecto = $this->getOwnerRecord();
                        if (! $proyecto || $proyecto->cerrado) {
                            return;
                        }

                        $path = $data['csv'] ?? null;
                        if (! $path) {
                            return;
                        }

                        $disk = Storage::disk('local');
                        $fullPath = $disk->path($path);

                        try {
                            $dias = $this->parseClockifyCsv($fullPath);

                            if (empty($dias)) {
                                Notification::make()
                                    ->title('No se encontraron filas válidas en el CSV.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            DB::transaction(function () use ($proyecto, $dias) {
                                foreach ($dias as $dia) {
                                    ProyectoTarea::create([
                                        'proyecto_id' => $proyecto->id,
                                        'descripcion' => $this->buildClockifyDescripcion($dia['date']),
                                        'fecha' => $dia['date']->toDateString(),
                                        'inicio' => $this->minutesToTime($dia['inicio']),
                                        'fin' => $this->minutesToTime($dia['fin']),
                                        'duracion' => $dia['duracion'],
                                        'precio' => $proyecto->precio_hora ?? 15,
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Importación completada.')
                                ->body(sprintf('Se han creado %d tareas.', count($dias)))
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('Error al importar el CSV.')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        } finally {
                            $disk->delete($path);
                        }
                    }),
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
                        ->form([
                            \Filament\Forms\Components\Select::make('modo')
                                ->label('Modo de facturación')
                                ->options([
                                    'por_tarea' => 'Una fila por tarea',
                                    'total' => 'Una fila por el total',
                                ])
                                ->default('por_tarea')
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records, array $data) => $this->facturarTareas($records, $data['modo'] ?? 'por_tarea')),
                    DeleteBulkAction::make()
                        ->disabled(fn (Collection $records) => $records->where('facturado', true)->count() > 0),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (ProyectoTarea $record) => ! $record->facturado
            );
    }

    protected function facturarTareas(Collection $records, string $modo = 'por_tarea'): void
    {
        $proyecto = $this->getOwnerRecord();
        $proyecto->loadMissing('cliente');

        $tareas = $records
            ->filter(fn (ProyectoTarea $tarea) => ! $tarea->facturado)
            ->values();

        if ($tareas->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($proyecto, $tareas, $modo) {
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
            if ($modo === 'total') {
                $totalHoras = (float) $tareas->sum(fn (ProyectoTarea $tarea) => (float) ($tarea->duracion ?? 0));
                $importeTotal = (float) $tareas->sum(fn (ProyectoTarea $tarea) => (float) ($tarea->duracion ?? 0) * (float) ($tarea->precio ?? 0));
                $precioUnitario = $totalHoras > 0 ? ($importeTotal / $totalHoras) : 0;

                FacturaLinea::create([
                    'factura_id' => $factura->id,
                    'orden' => $orden++,
                    'producto' => 0,
                    'concepto' => 'Servicios del proyecto ' . ($proyecto->nombre ?? ''),
                    'cantidad' => $totalHoras > 0 ? $totalHoras : 1,
                    'precio_unitario' => $precioUnitario,
                    'descuento_pct' => 0,
                    'impuesto_id' => $impuestoIva?->id,
                ]);
            } else {
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

    protected function getTareasStats(): array
    {
        $totales = $this->getTotalesAcumulados();
        $horas = number_format($totales['horas'], 2, ',', '.');
        $importe = number_format($totales['importe'], 2, ',', '.');

        return [
            [
                'label' => 'Horas acumuladas',
                'value' => $horas,
            ],
            [
                'label' => 'Importe acumulado',
                'value' => $importe . ' €',
            ],
        ];
    }

    protected function getTotalesAcumulados(): array
    {
        $proyecto = $this->getOwnerRecord();
        if (! $proyecto) {
            return ['horas' => 0.0, 'importe' => 0.0];
        }

        $totales = $proyecto->tareas()
            ->selectRaw('COALESCE(SUM(duracion), 0) as horas, COALESCE(SUM(duracion * precio), 0) as importe')
            ->first();

        return [
            'horas' => (float) ($totales->horas ?? 0),
            'importe' => (float) ($totales->importe ?? 0),
        ];
    }

    protected function parseClockifyCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [];
        }

        $delimiter = $this->detectCsvDelimiter($firstLine);
        $header = str_getcsv($firstLine, $delimiter, '"');
        $header[0] = ltrim((string) ($header[0] ?? ''), "\xEF\xBB\xBF");

        $indexes = $this->resolveClockifyIndexes($header);

        $dias = [];
        $line = 1;

        while (($row = fgetcsv($handle, 0, $delimiter, '"')) !== false) {
            $line++;
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }

            $fechaInicioRaw = trim((string) ($row[$indexes['Fecha de inicio']] ?? ''));
            $horaInicioRaw = trim((string) ($row[$indexes['Hora de inicio']] ?? ''));
            $horaFinRaw = trim((string) ($row[$indexes['Hora de finalización']] ?? ''));

            if ($fechaInicioRaw === '' || $horaInicioRaw === '' || $horaFinRaw === '') {
                continue;
            }

            $fecha = $this->parseClockifyDate($fechaInicioRaw, $line);
            $inicioMin = $this->parseClockifyTimeToMinutes($horaInicioRaw, $line);
            $finMin = $this->parseClockifyTimeToMinutes($horaFinRaw, $line);

            $duracionRaw = trim((string) ($row[$indexes['Duración (decimal)']] ?? ''));
            $duracion = $this->parseClockifyDurationDecimal($duracionRaw, $inicioMin, $finMin);

            $key = $fecha->toDateString();
            if (! isset($dias[$key])) {
                $dias[$key] = [
                    'date' => $fecha->copy()->startOfDay(),
                    'inicio' => $inicioMin,
                    'fin' => $inicioMin,
                    'duracion' => 0.0,
                ];
            }

            $dias[$key]['inicio'] = min($dias[$key]['inicio'], $inicioMin);
            $dias[$key]['duracion'] = round($dias[$key]['duracion'] + $duracion, 2);
        }

        fclose($handle);
        ksort($dias);

        foreach ($dias as &$dia) {
            $dia['fin'] = $this->calculateEndFromInicioDuracion($dia['inicio'], $dia['duracion']);
        }
        unset($dia);

        return array_values($dias);
    }

    protected function detectCsvDelimiter(string $line): string
    {
        $comma = substr_count($line, ',');
        $semicolon = substr_count($line, ';');

        return $semicolon > $comma ? ';' : ',';
    }

    protected function resolveClockifyIndexes(array $header): array
    {
        $positions = array_flip(array_map('trim', $header));
        $required = [
            'Fecha de inicio',
            'Hora de inicio',
            'Fecha de finalización',
            'Hora de finalización',
            'Duración (decimal)',
        ];

        foreach ($required as $column) {
            if (! array_key_exists($column, $positions)) {
                throw new \RuntimeException(sprintf('Falta la columna requerida "%s" en el CSV.', $column));
            }
        }

        return $positions;
    }

    protected function parseClockifyDate(string $value, int $line): Carbon
    {
        $value = trim($value);
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $exception) {
                // Try next format.
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            throw new \RuntimeException(sprintf('Fecha inválida en la línea %d: %s', $line, $value));
        }
    }

    protected function parseClockifyTimeToMinutes(string $value, int $line): int
    {
        $value = trim($value);
        $formats = ['H:i:s', 'H:i'];

        foreach ($formats as $format) {
            try {
                $time = Carbon::createFromFormat($format, $value);
                return ($time->hour * 60) + $time->minute;
            } catch (\Throwable $exception) {
                // Try next format.
            }
        }

        throw new \RuntimeException(sprintf('Hora inválida en la línea %d: %s', $line, $value));
    }

    protected function parseClockifyDurationDecimal(string $value, int $inicioMin, int $finMin): float
    {
        $value = str_replace(',', '.', $value);
        if ($value !== '' && is_numeric($value)) {
            return round((float) $value, 2);
        }

        if ($finMin < $inicioMin) {
            return 0.0;
        }

        return round(($finMin - $inicioMin) / 60, 2);
    }

    protected function calculateEndFromInicioDuracion(int $inicioMin, float $duracionHoras): int
    {
        $minutos = $inicioMin + (int) round($duracionHoras * 60);

        return max(0, min(1439, $minutos));
    }

    protected function minutesToTime(int $minutes): string
    {
        $minutes = max(0, min(1439, $minutes));
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    protected function buildClockifyDescripcion(Carbon $date): string
    {
        $date = $date->copy()->locale('es');
        $diaSemana = $date->translatedFormat('l');
        $dia = $date->translatedFormat('j');
        $mes = $date->translatedFormat('F');

        return sprintf('Desarrollos del %s, %s de %s', $diaSemana, $dia, $mes);
    }
}
