# Plugin: Proyectos (Projects)

Time-tracking and project management with billable task entries. Supports hourly rate billing and tracks which tasks have already been invoiced.

## Enable / disable

```dotenv
PLUGIN_PROYECTOS_ENABLED=true   # default: true
```

> Disabling this plugin also removes the projects section from `portal-clientes`.

## Models

### `Proyectos\Models\Proyecto`

| Field | Type | Description |
|---|---|---|
| `cliente_id` | FK | Associated client |
| `nombre` | string | Project name |
| `descripcion` | text | Optional description |
| `fecha_inicio` | date | Start date |
| `fecha_fin` | date | End date |
| `precio_hora` | decimal(10,2) | Default hourly rate for tasks |
| `cerrado` | boolean | Whether the project is closed to new entries |

### `Proyectos\Models\ProyectoTarea`

| Field | Type | Description |
|---|---|---|
| `proyecto_id` | FK | Parent project |
| `descripcion` | text | Task description |
| `fecha` | date | Work date |
| `inicio` | time | Start time |
| `fin` | time | End time |
| `duracion` | decimal(10,2) | Hours worked |
| `precio` | decimal(10,2) | Price override (falls back to project rate) |
| `facturado` | boolean | Whether this task has been billed |

## Filament resources

- **ProyectoResource** — project CRUD with nested task management
- **ProyectoTareaResource** — standalone task list view

## Database migrations

| Migration | Creates table |
|---|---|
| `create_proyectos_table` | `proyectos` |
| `create_proyectos_tareas_table` | `proyectos_tareas` |

## Dependencies

None (but `portal-clientes` optionally uses this plugin).
