{{-- Componente de renderizado de una gráfica individual --}}
@if($grafica->isStat())
    <div class="flex flex-wrap gap-4">
        @forelse($stats as $stat)
            <div class="flex-1 min-w-[160px] rounded-xl p-5 text-center border"
                 style="border-left: 4px solid {{ $stat['color'] }}; background-color: {{ $stat['color'] }}15;">

                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                    {{ $stat['nombre'] }}
                </div>

                <div class="text-3xl font-bold tabular-nums"
                     style="color: {{ $stat['color'] }}">
                    @if($stat['campo_y'] === '__count__' || $stat['agregacion'] === 'count')
                        {{ number_format($stat['value'], 0, ',', '.') }}
                    @else
                        {{ number_format($stat['value'], 2, ',', '.') }}
                    @endif
                </div>

                <div class="text-xs text-gray-400 mt-1">
                    {{ match($stat['agregacion']) {
                        'sum'    => 'Suma total',
                        'count'  => 'Número de registros',
                        'avg'    => 'Media',
                        'median' => 'Mediana',
                        default  => strtoupper($stat['agregacion']),
                    } }}
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Sin fuentes de datos configuradas.</p>
        @endforelse
    </div>

@else
    <div style="background: #ffffff; border-radius: 0.5rem; padding: 0.5rem;">
        <canvas id="grafica-canvas-{{ $grafica->id }}" height="350"></canvas>
        @script
        <script>
            (function () {
                var canvasId = 'grafica-canvas-{{ $grafica->id }}';
                var config   = {!! \Illuminate\Support\Js::from($chartOptions) !!};

                function render() {
                    var el = document.getElementById(canvasId);
                    if (el) new Chart(el, config);
                }

                if (typeof Chart !== 'undefined') {
                    render();
                } else {
                    var s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
                    s.onload = render;
                    document.head.appendChild(s);
                }
            })();
        </script>
        @endscript
    </div>
@endif
