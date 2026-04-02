@php
    /** @var \Informes\Filament\Resources\InformeResource\Pages\ViewInforme $this */
    $dataService = app(\Informes\Services\GraficaDataService::class);
@endphp

<x-filament-panels::page>

    {{-- Cabecera del informe --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-12">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ $this->record->nombre }}
        </h2>

        @if($this->record->descripcion)
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ $this->record->descripcion }}
            </p>
        @endif

        <p class="mt-3 text-xs text-gray-400">
            {{ $this->record->graficas->count() }} gráfica(s) —
            creado el {{ $this->record->created_at->format('d/m/Y') }}
        </p>
    </div>

    {{-- Grid de gráficas --}}
    @if($this->record->graficas->isEmpty())
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-12 text-center">
            <x-filament::icon
                icon="heroicon-o-chart-bar"
                class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600"
            />
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Este informe no tiene gráficas todavía.
            </p>
            <p class="mt-1 text-xs text-gray-400">
                Usa el botón <strong>Nueva gráfica</strong> para añadir la primera.
            </p>
        </div>
    @else
        @php
            $graficasOrdenadas = $this->record->graficas->sortBy([
                fn($a, $b) => ($a->orden > 0 ? $a->orden : PHP_INT_MAX) <=> ($b->orden > 0 ? $b->orden : PHP_INT_MAX),
                fn($a, $b) => $a->id <=> $b->id,
            ]);
        @endphp
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            @foreach($graficasOrdenadas as $grafica)
                @php
                    $isStat       = $grafica->isStat();
                    $graficaError = null;
                    $stats        = [];
                    $chartOptions = [];
                    try {
                        $stats        = $isStat ? $dataService->buildStatData($grafica) : [];
                        $chartOptions = !$isStat ? $dataService->buildApexOptions($grafica) : [];
                    } catch (\Throwable $e) {
                        $graficaError = $e->getMessage();
                    }
                    $colSpan = match((int)($grafica->ancho ?? 50)) {
                        100 => 'lg:col-span-4',
                        75  => 'lg:col-span-3',
                        25  => 'lg:col-span-1',
                        default => 'lg:col-span-2',
                    };
                @endphp

                <div wire:key="grafica-{{ $grafica->id }}" class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-5 {{ $colSpan }}">

                    {{-- Cabecera de la tarjeta --}}
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                                {{ $grafica->nombre }}
                            </h3>
                            <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-400">
                                <span class="capitalize">{{ str_replace('_', ' ', $grafica->tipo) }}</span>

                                @if($grafica->fecha_desde || $grafica->fecha_hasta)
                                    <span>·</span>
                                    <span>
                                        {{ $grafica->fecha_desde?->format('d/m/Y') ?? '…' }}
                                        –
                                        {{ $grafica->fecha_hasta?->format('d/m/Y') ?? '…' }}
                                    </span>
                                @endif

                                @if($grafica->granularidad)
                                    <span>·</span>
                                    <span>por {{ match($grafica->granularidad) {
                                        'dia'    => 'día',
                                        'semana' => 'semana',
                                        'mes'    => 'mes',
                                        'anio'   => 'año',
                                        default  => $grafica->granularidad,
                                    } }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Acciones directas sobre la gráfica --}}
                        <div class="flex items-center gap-2 shrink-0 ml-4" x-data>
                            <button
                                type="button"
                                x-on:click="$wire.mountAction('editar_grafica', { graficaId: {{ $grafica->id }} })"
                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-950 transition-colors"
                            >
                                <x-filament::icon icon="heroicon-m-pencil" class="h-3.5 w-3.5" />
                                Editar
                            </button>

                            <button
                                type="button"
                                x-on:click="$wire.mountAction('eliminar_grafica', { graficaId: {{ $grafica->id }} })"
                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-950 transition-colors"
                            >
                                <x-filament::icon icon="heroicon-m-trash" class="h-3.5 w-3.5" />
                                Eliminar
                            </button>
                        </div>
                    </div>

                    {{-- Contenido de la gráfica --}}
                    @if($graficaError)
                        <div class="rounded-lg bg-danger-50 dark:bg-danger-950 border border-danger-200 dark:border-danger-800 p-4">
                            <div class="flex items-start gap-3">
                                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 text-danger-500 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm font-medium text-danger-700 dark:text-danger-300">Error al cargar la gráfica</p>
                                    <p class="mt-1 text-xs text-danger-600 dark:text-danger-400 font-mono break-all">{{ $graficaError }}</p>
                                </div>
                            </div>
                        </div>
                    @elseif($isStat)
                        {{-- Cifras resumen con estilos de Filament StatsOverviewWidget --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-1 lg:grid-cols-1">
                            @forelse($stats as $stat)
                                <div class="fi-wi-stats-overview-stat">
                                    <div class="fi-wi-stats-overview-stat-content">
                                        <div class="fi-wi-stats-overview-stat-label-ctn">
                                            <span class="fi-wi-stats-overview-stat-label">
                                                {{ $stat['nombre'] }}
                                            </span>
                                        </div>
                                        <div class="fi-wi-stats-overview-stat-value">
                                            @if($stat['campo_y'] === '__count__' || $stat['agregacion'] === 'count')
                                                {{ number_format($stat['value'], 0, ',', '.') }}
                                            @else
                                                {{ number_format($stat['value'], 2, ',', '.') }}
                                            @endif
                                        </div>
                                        <div class="fi-wi-stats-overview-stat-description">
                                            {{ match($stat['agregacion']) {
                                                'sum'    => 'Suma total',
                                                'count'  => 'Número de registros',
                                                'avg'    => 'Media',
                                                'median' => 'Mediana',
                                                default  => strtoupper($stat['agregacion']),
                                            } }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400">Sin fuentes de datos configuradas.</p>
                            @endforelse
                        </div>

                    @else
                        <div
                            wire:ignore
                            x-data="{
                                chart: null,
                                init() {
                                    this.$nextTick(() => {
                                        if (typeof Chart !== 'undefined') {
                                            this.chart = new Chart(
                                                this.$refs.chartEl,
                                                {{ \Illuminate\Support\Js::from($chartOptions) }}
                                            );
                                        }
                                    });
                                },
                                destroy() {
                                    this.chart?.destroy();
                                }
                            }"
                        >
                            <canvas x-ref="chartEl" height="350"></canvas>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif

</x-filament-panels::page>
