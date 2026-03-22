<?php

namespace Informes\Filament\Resources\InformeResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Informes\Filament\Resources\InformeResource;
use Informes\Models\Grafica;
use Informes\Models\GraficaFuente;
use Informes\Services\DataSourceRegistry;

class ViewInforme extends ViewRecord
{
    protected static string $resource = InformeResource::class;

    public function getView(): string
    {
        return 'informes::pages.view-informe';
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record->load('graficas.fuentes');
    }

    // -------------------------------------------------------------------------
    // Cachear acciones de header + acciones ocultas por gráfica
    // -------------------------------------------------------------------------

    public function cacheInteractsWithHeaderActions(): void
    {
        parent::cacheInteractsWithHeaderActions();

        // Registrar en cachedActions sin añadir al header ni marcar como hidden/disabled
        $this->cacheAction($this->getEditarGraficaAction());
        $this->cacheAction($this->getEliminarGraficaAction());
    }

    // -------------------------------------------------------------------------
    // Acciones visibles en el header
    // -------------------------------------------------------------------------

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('nueva_grafica')
                ->label('Nueva gráfica')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->color('primary')
                ->form($this->getGraficaFormFields())
                ->action(fn(array $data) => $this->saveGrafica($data))
                ->slideOver()
                ->modalWidth('4xl'),
        ];
    }

    // -------------------------------------------------------------------------
    // Acciones por gráfica (registradas en caché, no en header)
    // -------------------------------------------------------------------------

    private function getEditarGraficaAction(): Action
    {
        return Action::make('editar_grafica')
            ->form($this->getGraficaFormFields())
            ->fillForm(function (array $arguments): array {
                $grafica = Grafica::with('fuentes')->find($arguments['graficaId'] ?? null);
                if (!$grafica) return [];

                return array_merge(
                    $grafica->only(['nombre', 'tipo', 'granularidad', 'orden', 'ancho', 'combinar', 'etiqueta_combinada']),
                    [
                        'fecha_desde' => $grafica->fecha_desde?->format('Y-m-d'),
                        'fecha_hasta' => $grafica->fecha_hasta?->format('Y-m-d'),
                        'fuentes'     => $grafica->fuentes->map(fn($f) => $f->only([
                            'modelo', 'nombre_display', 'color',
                            'campo_x', 'campo_y', 'agregacion_y', 'orden', 'signo',
                        ]))->all(),
                    ]
                );
            })
            ->action(function (array $data, array $arguments): void {
                $this->saveGrafica($data, (int) ($arguments['graficaId'] ?? 0) ?: null);
            })
            ->slideOver()
            ->modalWidth('4xl')
            ->modalHeading('Editar gráfica');
    }

    private function getEliminarGraficaAction(): Action
    {
        return Action::make('eliminar_grafica')
            ->requiresConfirmation()
            ->modalHeading('¿Eliminar gráfica?')
            ->modalDescription('Esta acción no se puede deshacer.')
            ->color('danger')
            ->action(function (array $arguments): void {
                $graficaId = $arguments['graficaId'] ?? null;
                if ($graficaId) {
                    Grafica::find($graficaId)?->delete();
                    $this->record->load('graficas.fuentes');
                    Notification::make()->title('Gráfica eliminada')->success()->send();
                }
            });
    }

    // -------------------------------------------------------------------------
    // Guardado de gráfica (create o update)
    // -------------------------------------------------------------------------

    private function saveGrafica(array $data, ?int $graficaId = null): void
    {
        $ordenForm = (int) ($data['orden'] ?? 0);
        $graficaData = [
            'informe_id'  => $this->record->id,
            'nombre'      => $data['nombre'],
            'tipo'        => $data['tipo'],
            'fecha_desde' => $data['fecha_desde'] ?? null,
            'fecha_hasta' => $data['fecha_hasta'] ?? null,
            'granularidad' => in_array($data['tipo'], Grafica::TIPOS_SIN_GRANULARIDAD)
                ? null
                : ($data['granularidad'] ?? 'mes'),
            'orden' => $ordenForm > 0
                ? $ordenForm
                : ($graficaId
                    ? (Grafica::find($graficaId)?->orden ?? 0)
                    : ($this->record->graficas()->max('orden') ?? 0) + 1),
            'ancho'              => (int) ($data['ancho'] ?? 50),
            'combinar'           => (bool) ($data['combinar'] ?? false),
            'etiqueta_combinada' => $data['etiqueta_combinada'] ?? null,
        ];

        if ($graficaId) {
            $grafica = Grafica::findOrFail($graficaId);
            $grafica->update($graficaData);
            $grafica->fuentes()->delete();
        } else {
            $grafica = Grafica::create($graficaData);
        }

        foreach ($data['fuentes'] ?? [] as $idx => $fuenteData) {
            if (($fuenteData['campo_y'] ?? '') === '__count__') {
                $fuenteData['agregacion_y'] = 'count';
            }

            if (in_array($data['tipo'], Grafica::TIPOS_SIN_GRANULARIDAD)) {
                $fuenteData['campo_x'] = $fuenteData['campo_x'] ?? null;
            }

            GraficaFuente::create([
                'grafica_id'    => $grafica->id,
                'modelo'        => $fuenteData['modelo'],
                'nombre_display' => $fuenteData['nombre_display'],
                'color'         => $fuenteData['color'] ?? null,
                'campo_x'       => $fuenteData['campo_x'] ?? null,
                'campo_y'       => $fuenteData['campo_y'],
                'agregacion_y'  => $fuenteData['agregacion_y'] ?? 'sum',
                'orden'         => $idx,
                'signo'         => (int) ($fuenteData['signo'] ?? 1),
            ]);
        }

        $this->record->load('graficas.fuentes');

        Notification::make()
            ->title($graficaId ? 'Gráfica actualizada' : 'Gráfica creada')
            ->success()
            ->send();
    }

    // -------------------------------------------------------------------------
    // Formulario de gráfica (compartido por crear y editar)
    // -------------------------------------------------------------------------

    private function getGraficaFormFields(): array
    {
        return [
            Section::make('Configuración general')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la gráfica')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Select::make('tipo')
                        ->label('Tipo de gráfica')
                        ->options([
                            'line'           => 'Línea',
                            'bar'            => 'Barras verticales',
                            'bar_horizontal' => 'Barras horizontales',
                            'area'           => 'Área',
                            'pie'            => 'Tarta',
                            'donut'          => 'Donut',
                            'scatter'        => 'Dispersión',
                            'stat'           => 'Cifra resumen',
                        ])
                        ->required()
                        ->default('line')
                        ->live(),

                    DatePicker::make('fecha_desde')
                        ->label('Fecha desde')
                        ->displayFormat('d/m/Y'),

                    DatePicker::make('fecha_hasta')
                        ->label('Fecha hasta')
                        ->displayFormat('d/m/Y')
                        ->afterOrEqual('fecha_desde'),

                    Select::make('granularidad')
                        ->label('Agrupar por')
                        ->options([
                            'dia'    => 'Día',
                            'semana' => 'Semana',
                            'mes'    => 'Mes',
                            'anio'   => 'Año',
                        ])
                        ->default('mes')
                        ->hidden(fn($get) => in_array($get('tipo'), Grafica::TIPOS_SIN_GRANULARIDAD))
                        ->columnSpanFull(),

                    Select::make('ancho')
                        ->label('Ancho')
                        ->options([
                            100 => '100% — Ancho completo',
                            75  => '75%',
                            50  => '50% — Medio',
                            25  => '25%',
                        ])
                        ->default(50)
                        ->required(),

                    TextInput::make('orden')
                        ->label('Orden')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->placeholder('Auto')
                        ->helperText('Deja en blanco o 0 para orden automático por ID'),

                    Toggle::make('combinar')
                        ->label('Combinar fuentes en un solo resultado')
                        ->helperText('Suma/resta todas las fuentes en un único valor')
                        ->hidden(fn($get) => $get('tipo') !== 'stat')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('etiqueta_combinada')
                        ->label('Etiqueta del resultado combinado')
                        ->placeholder('Ej: Balance neto')
                        ->hidden(fn($get) => !$get('combinar') || $get('tipo') !== 'stat')
                        ->columnSpanFull(),
                ]),

            Section::make('Fuentes de datos')
                ->description('Añade una o más series de datos para la gráfica.')
                ->schema([
                    Repeater::make('fuentes')
                        ->label('')
                        ->addActionLabel('+ Añadir fuente de datos')
                        ->schema([
                            Select::make('modelo')
                                ->label('Modelo')
                                ->options(DataSourceRegistry::getModelOptions())
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('campo_x', null);
                                    $set('campo_y', null);
                                }),

                            TextInput::make('nombre_display')
                                ->label('Nombre para mostrar')
                                ->required()
                                ->placeholder('Ej: Facturación 2026'),

                            ColorPicker::make('color')
                                ->label('Color'),

                            Select::make('signo')
                                ->label('Operación')
                                ->options([1 => '+ Sumar', -1 => '− Restar'])
                                ->default(1)
                                ->hidden(fn($get) => !$get('../../combinar') || $get('../../tipo') !== 'stat'),

                            Select::make('campo_x')
                                ->label(fn($get) => in_array($get('../../tipo'), Grafica::TIPOS_SIN_GRANULARIDAD)
                                    ? 'Campo de fecha (filtro)'
                                    : 'Eje X (campo de fecha)')
                                ->options(fn($get) => DataSourceRegistry::getDateFields($get('modelo') ?? ''))
                                ->hidden(fn($get) => empty($get('modelo')))
                                ->live(),

                            Select::make('campo_y')
                                ->label(fn($get) => in_array($get('../../tipo'), Grafica::TIPOS_SIN_GRANULARIDAD)
                                    ? 'Valor a calcular'
                                    : 'Eje Y (valor)')
                                ->options(fn($get) => DataSourceRegistry::getNumericFields($get('modelo') ?? ''))
                                ->required()
                                ->hidden(fn($get) => empty($get('modelo')))
                                ->live(),

                            Select::make('agregacion_y')
                                ->label('Agregación')
                                ->options([
                                    'sum'    => 'Suma',
                                    'count'  => 'Conteo',
                                    'avg'    => 'Media (promedio)',
                                    'median' => 'Mediana',
                                ])
                                ->default('sum')
                                ->required()
                                ->hidden(fn($get) => $get('campo_y') === '__count__' || empty($get('modelo'))),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn(array $state) => $state['nombre_display'] ?? 'Nueva fuente')
                        ->minItems(1),
                ]),
        ];
    }
}
