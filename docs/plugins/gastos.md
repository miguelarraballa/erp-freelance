# Plugin: Gastos (Expenses)

Tracks business expenses in a categorised ledger. Used for internal cost monitoring; expense records are independent from the invoicing system.

## Enable / disable

```dotenv
PLUGIN_GASTOS_ENABLED=true   # default: true
```

## Models

### `Gastos\Models\Gasto`

| Field | Type | Description |
|---|---|---|
| `nombre` | string | Expense name / title |
| `descripcion` | text | Optional description |
| `categoria` | string | Category label |
| `fecha` | date | Expense date |
| `importe` | decimal(10,2) | Amount |

## Filament resources

- **GastoResource** — CRUD panel for expense records under the admin panel

## Database migrations

| Migration | Creates table |
|---|---|
| `create_gastos_table` | `gastos` |

## Dependencies

None.
