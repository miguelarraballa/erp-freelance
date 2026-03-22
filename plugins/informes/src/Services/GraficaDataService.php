<?php

namespace Informes\Services;

use Informes\Models\Grafica;
use Informes\Models\GraficaFuente;
use Illuminate\Database\Eloquent\Builder;

/**
 * Genera los datos y opciones de Chart.js para una Gráfica.
 */
class GraficaDataService
{
    /**
     * Construye el array de opciones completo para Chart.js
     * (o los datos de una cifra resumen para tipo 'stat').
     */
    public function buildApexOptions(Grafica $grafica): array
    {
        $grafica->loadMissing('fuentes');

        if ($grafica->isStat()) {
            return []; // Las stat no usan Chart.js; ver buildStatData()
        }

        if (in_array($grafica->tipo, ['pie', 'donut'])) {
            return $this->buildPieOptions($grafica);
        }

        return $this->buildSeriesOptions($grafica);
    }

    /**
     * Devuelve array de cifras resumen para tipo 'stat'.
     * Formato: [['nombre' => ..., 'value' => ..., 'color' => ..., 'agregacion' => ...]]
     */
    public function buildStatData(Grafica $grafica): array
    {
        $grafica->loadMissing('fuentes');

        return $grafica->fuentes->map(fn(GraficaFuente $fuente) => [
            'nombre'    => $fuente->nombre_display,
            'color'     => $fuente->color ?? '#020BFF',
            'value'     => $this->calculateAggregate($fuente, $grafica),
            'agregacion' => $fuente->agregacion_y,
            'campo_y'   => $fuente->campo_y,
        ])->all();
    }

    // -------------------------------------------------------------------------
    // Charts de series (line, bar, area, scatter, bar_horizontal)
    // -------------------------------------------------------------------------

    private function buildSeriesOptions(Grafica $grafica): array
    {
        $allCategories = [];
        $rawSeries = [];

        foreach ($grafica->fuentes as $fuente) {
            $data = $this->querySeriesData($fuente, $grafica);
            $allCategories = array_merge($allCategories, array_keys($data));
            $rawSeries[] = [
                'name'  => $fuente->nombre_display,
                'color' => $fuente->color,
                'data'  => $data,
            ];
        }

        $allCategories = array_values(array_unique($allCategories));
        sort($allCategories);

        $isArea       = $grafica->tipo === 'area';
        $isBar        = in_array($grafica->tipo, ['bar', 'bar_horizontal']);
        $isHorizontal = $grafica->tipo === 'bar_horizontal';

        $datasets = [];
        foreach ($rawSeries as $s) {
            $values = array_map(
                fn($cat) => round((float) ($s['data'][$cat] ?? 0), 2),
                $allCategories
            );

            $dataset = [
                'label'       => $s['name'],
                'data'        => $values,
                'borderWidth' => 2,
                'tension'     => 0.4,
            ];

            if ($s['color']) {
                $dataset['borderColor'] = $s['color'];
                if ($isBar) {
                    $dataset['backgroundColor'] = $s['color'];
                } elseif ($isArea) {
                    $dataset['backgroundColor'] = $s['color'] . '33';
                    $dataset['fill']            = true;
                } else {
                    $dataset['backgroundColor'] = $s['color'];
                    $dataset['pointBackgroundColor'] = $s['color'];
                }
            }

            $datasets[] = $dataset;
        }

        $chartType = match ($grafica->tipo) {
            'bar', 'bar_horizontal' => 'bar',
            'scatter'               => 'scatter',
            default                 => 'line',
        };

        $config = [
            'type' => $chartType,
            'data' => [
                'labels'   => $allCategories,
                'datasets' => $datasets,
            ],
            'options' => [
                'responsive'          => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend'  => ['position' => 'top'],
                    'tooltip' => ['mode' => 'index', 'intersect' => false],
                ],
            ],
        ];

        if ($isHorizontal) {
            $config['options']['indexAxis'] = 'y';
        }

        return $config;
    }

    // -------------------------------------------------------------------------
    // Charts de tarta (pie, donut)
    // -------------------------------------------------------------------------

    private function buildPieOptions(Grafica $grafica): array
    {
        $labels  = [];
        $series  = [];
        $colors  = [];

        foreach ($grafica->fuentes as $fuente) {
            $labels[] = $fuente->nombre_display;
            $series[] = round((float) $this->calculateAggregate($fuente, $grafica), 2);
            if ($fuente->color) {
                $colors[] = $fuente->color;
            }
        }

        $chartType = $grafica->tipo === 'donut' ? 'doughnut' : 'pie';

        $dataset = ['data' => $series];
        if (!empty($colors)) {
            $dataset['backgroundColor'] = $colors;
        }

        return [
            'type' => $chartType,
            'data' => [
                'labels'   => $labels,
                'datasets' => [$dataset],
            ],
            'options' => [
                'responsive'          => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['position' => 'bottom'],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Consultas de datos
    // -------------------------------------------------------------------------

    /**
     * Consulta los datos de una fuente agrupados por período temporal.
     * Devuelve ['2026-01' => 1234.56, '2026-02' => 987.00, ...]
     */
    private function querySeriesData(GraficaFuente $fuente, Grafica $grafica): array
    {
        $class = DataSourceRegistry::getModelClass($fuente->modelo);
        if (!$class || !$fuente->campo_x) {
            return [];
        }

        $query = $class::query();
        $this->applyDateFilters($query, $fuente->campo_x, $grafica);

        $granularidad = $grafica->granularidad ?? 'mes';
        $groupExpr    = $this->getGroupExpression($fuente->campo_x, $granularidad);

        if ($fuente->campo_y === '__count__' || $fuente->agregacion_y === 'count') {
            return $query
                ->selectRaw("{$groupExpr} as period, COUNT(*) as value")
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('value', 'period')
                ->toArray();
        }

        if ($fuente->agregacion_y === 'median') {
            return $this->queryMedianGrouped($query, $fuente->campo_x, $fuente->campo_y, $groupExpr);
        }

        $aggFunc = strtoupper($fuente->agregacion_y);
        return $query
            ->selectRaw("{$groupExpr} as period, {$aggFunc}({$fuente->campo_y}) as value")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('value', 'period')
            ->toArray();
    }

    /**
     * Calcula el valor agregado para una fuente (sin agrupar por período).
     * Usado por stat, pie y donut.
     */
    private function calculateAggregate(GraficaFuente $fuente, Grafica $grafica): float|int
    {
        $class = DataSourceRegistry::getModelClass($fuente->modelo);
        if (!$class) {
            return 0;
        }

        $query = $class::query();

        if ($fuente->campo_x) {
            $this->applyDateFilters($query, $fuente->campo_x, $grafica);
        }

        if ($fuente->campo_y === '__count__' || $fuente->agregacion_y === 'count') {
            return $query->count();
        }

        if ($fuente->agregacion_y === 'median') {
            $values = $query->orderBy($fuente->campo_y)->pluck($fuente->campo_y);
            $count  = $values->count();
            if ($count === 0) {
                return 0;
            }
            if ($count % 2 === 0) {
                return ($values[$count / 2 - 1] + $values[$count / 2]) / 2;
            }
            return (float) $values[intdiv($count, 2)];
        }

        return match ($fuente->agregacion_y) {
            'sum'  => round((float) $query->sum($fuente->campo_y), 2),
            'avg'  => round((float) $query->avg($fuente->campo_y), 2),
            default => round((float) $query->sum($fuente->campo_y), 2),
        };
    }

    /**
     * Calcula la mediana agrupada por período.
     */
    private function queryMedianGrouped(
        Builder $query,
        string $campoX,
        string $campoY,
        string $groupExpr
    ): array {
        $rows = $query
            ->selectRaw("{$groupExpr} as period, {$campoY} as value")
            ->orderBy('period')
            ->get();

        $result = [];
        foreach ($rows->groupBy('period') as $period => $items) {
            $values = $items->pluck('value')->sort()->values();
            $count  = $values->count();
            if ($count === 0) {
                $result[$period] = 0;
            } elseif ($count % 2 === 0) {
                $result[$period] = ($values[$count / 2 - 1] + $values[$count / 2]) / 2;
            } else {
                $result[$period] = (float) $values[intdiv($count, 2)];
            }
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function applyDateFilters(Builder $query, string $campoFecha, Grafica $grafica): void
    {
        if ($grafica->fecha_desde) {
            $query->whereDate($campoFecha, '>=', $grafica->fecha_desde);
        }
        if ($grafica->fecha_hasta) {
            $query->whereDate($campoFecha, '<=', $grafica->fecha_hasta);
        }
    }

    private function getGroupExpression(string $field, string $granularidad): string
    {
        return match ($granularidad) {
            'dia'    => "DATE({$field})",
            'semana' => "DATE_FORMAT({$field}, '%Y-%u')",
            'mes'    => "DATE_FORMAT({$field}, '%Y-%m')",
            'anio'   => "YEAR({$field})",
            default  => "DATE_FORMAT({$field}, '%Y-%m')",
        };
    }
}
