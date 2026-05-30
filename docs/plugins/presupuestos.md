# Plugin: Presupuestos (Quotes)

Manages sales quotes with the same lifecycle as invoices: draft → issued → accepted/rejected. Quotes can be converted to invoices with a single action.

## Enable / disable

```dotenv
PLUGIN_PRESUPUESTOS_ENABLED=true   # default: true
```

> Disabling this plugin also removes the quotes section from `portal-clientes`.

## Models

### `Presupuestos\Models\Presupuesto`

Mirrors `App\Models\Factura`. Once issued and numbered, only `estado` may be updated.

| Field | Type | Description |
|---|---|---|
| `serie_id` | FK | Numbering series |
| `numero` | int | Auto-assigned number within series |
| `numero_completo` | string | Formatted number (prefix + number + suffix) |
| `cliente_id` | FK | Client |
| `fecha` | date | Issue date |
| `vencimiento` | date | Expiry date |
| `estado` | enum | borrador / emitida / aceptada / rechazada |
| `base` | decimal | Net total |
| `iva_total` | decimal | VAT total |
| `irpf_total` | decimal | IRPF withholding total |
| `total` | decimal | Grand total |
| `moneda` | string | Currency code |

### `Presupuestos\Models\PresupuestoLinea`

Line items. Stores pre-calculated `base_linea`, `iva_linea`, `irpf_linea`, `total_linea`.

## Routes

| Method | URL | Description |
|---|---|---|
| GET | `/presupuestos/{presupuesto}/pdf` | Download quote PDF |
| POST | `/presupuestos/{presupuesto}/facturar` | Convert quote to invoice |

## Filament resources

- **PresupuestoResource** — full CRUD with PDF preview and "Convert to Invoice" action

## Services

| Class | Responsibility |
|---|---|
| `PresupuestoService` | Business logic for quote creation and state transitions |
| `PresupuestoCalc` | Recalculates line totals and invoice-level aggregates |
| `PresupuestoEmisionService` | Handles numbering assignment on emission |

## Database migrations

| Migration | Action |
|---|---|
| `create_presupuestos_table` | `presupuestos` |
| `create_presupuestos_lineas_table` | `presupuesto_lineas` |
| `create_presupuestos_facturas_table` | `presupuestos_facturas` (pivot: quote → invoice) |

## Configuration

```php
// config/presupuestos.php
return [
    'enabled' => env('PLUGIN_PRESUPUESTOS_ENABLED', true),
];
```

## Dependencies

None (but `portal-clientes` optionally uses this plugin).
