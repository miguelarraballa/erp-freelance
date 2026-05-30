# Plugin: Informes (Reports & Charts)

Configurable reporting dashboard. Reports group one or more charts, each powered by one or more data sources. Charts are rendered with Chart.js via Livewire. All configuration is done through the Filament admin UI — no code changes needed to add a new report.

## Enable / disable

```dotenv
PLUGIN_INFORMES_ENABLED=true   # default: true
```

## Supported chart types

| Type | Description |
|---|---|
| `line` | Line chart with temporal X axis |
| `bar` | Bar chart with temporal X axis |
| `pie` | Pie chart (no granularity) |
| `donut` | Donut chart (no granularity) |
| `stat` | Single aggregated value (KPI card) |

## Models

### `Informes\Models\Informe`

A report container. Has a name, description, and an ordered list of charts.

### `Informes\Models\Grafica`

A chart within a report.

| Field | Description |
|---|---|
| `tipo` | Chart type (line, bar, pie, donut, stat) |
| `fecha_desde` / `fecha_hasta` | Date range for the chart |
| `granularidad` | Time bucket: `day`, `week`, or `month` |
| `ancho` | Column width in the report grid (1–12) |
| `combinar` | Combine all sources into a single stacked series |
| `etiqueta_combinada` | Label for the combined series |

### `Informes\Models\GraficaFuente`

A data source within a chart.

| Field | Description |
|---|---|
| `modelo` | Eloquent model class to query (e.g. `App\Models\Factura`) |
| `nombre_display` | Series label shown in the chart legend |
| `color` | Hex color code |
| `campo_x` | Field to use as the X axis (usually a date column) |
| `campo_y` | Field to aggregate for the Y axis |
| `agregacion_y` | SQL function: `SUM`, `COUNT`, `AVG`, `MIN`, `MAX` |
| `signo` | `1` or `-1` (invert values, e.g. for expenses) |
| `query_personalizada` | Optional raw SQL that replaces the model-based query |

## Custom queries

If `query_personalizada` is set, it overrides the model query entirely. The SQL must return two columns: `x` (the bucket value or label) and `y` (the aggregated value).

Example:
```sql
SELECT DATE_FORMAT(fecha, '%Y-%m') AS x, SUM(total) AS y
FROM facturas
WHERE estado IN ('emitida','cobrada')
GROUP BY x
ORDER BY x
```

## Livewire component

`informes-grafica` — renders a single chart using Chart.js. Registered automatically by the service provider.

## Database migrations

| Migration | Action |
|---|---|
| `create_informes_table` | `informes` |
| `create_graficas_table` | `graficas` |
| `create_grafica_fuentes_table` | `grafica_fuentes` |
| (+ alteration migrations) | Add columns as features were extended |

## Dependencies

None.
