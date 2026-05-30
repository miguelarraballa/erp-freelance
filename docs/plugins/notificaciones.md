# Plugin: Notificaciones (Email Notifications)

Queue-based email notification system with dynamic templates. Templates support tag placeholders (e.g. `{{clientes_contacto_nombre}}`) that are resolved from the related entity at send time. Supports optional PDF attachments.

## Enable / disable

```dotenv
PLUGIN_NOTIFICACIONES_ENABLED=true   # default: true
```

## Models

### `Notificaciones\Models\NotificacionPlantilla`

| Field | Description |
|---|---|
| `nombre` | Internal template name |
| `asunto` | Email subject (can contain tags) |
| `cuerpo_html` | HTML body (can contain tags) |
| `html_personalizado` | Boolean: uses custom layout instead of system wrapper |
| `cuerpo_texto` | Plain-text fallback |

### `Notificaciones\Models\Notificacion`

| Field | Description |
|---|---|
| `notificacion_plantilla_id` | FK to template |
| `email_destinatario` | Recipient address |
| `asunto_procesado` | Subject with tags already resolved |
| `cuerpo_html_procesado` | HTML with tags already resolved |
| `estado` | `EnCola` / `Enviado` / `Error` |
| `fecha_envio` | Timestamp of successful sending |
| `relacionado_tabla` / `relacionado_id` | The entity that triggered this notification |
| `adjuntable_type` / `adjuntable_id` | Polymorphic: the model whose PDF is attached |

### `Notificaciones\Models\NotificacionEtiqueta`

Global key-value tags available in all templates (e.g. company name, phone number).

## Artisan commands

| Command | Description |
|---|---|
| `notificaciones:enviar` | Process and send all queued notifications (`EnCola` state) |
| `notificaciones:recordar-facturas-vencidas` | Send overdue invoice reminder notifications |

Schedule both commands in your crontab:

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

Or add to `routes/console.php`:

```php
Schedule::command('notificaciones:enviar')->everyFiveMinutes();
Schedule::command('notificaciones:recordar-facturas-vencidas')->daily();
```

## Usage example

```php
use Notificaciones\Services\NotificacionesHelper;

NotificacionesHelper::queueEmail(
    plantillaNombre: 'Factura emitida',
    emailDestinatario: $cliente->email_facturacion,
    context: ['clientes' => $cliente, 'facturas' => $factura],
    adjuntable: $factura,   // optional: attaches the invoice PDF
);
```

## Available template tags

Tags follow the pattern `{{model_field}}`. Available models: `clientes`, `facturas`, `presupuestos`. Example tags:

- `{{clientes_contacto_nombre}}`
- `{{facturas_numero_completo}}`
- `{{facturas_total}}`

## Database migrations

| Migration | Creates table |
|---|---|
| `create_notificaciones_plantillas_table` | `notificaciones_plantillas` |
| `create_notificaciones_table` | `notificaciones` |
| `create_notificaciones_etiquetas_table` | `notificaciones_etiquetas` |

## Dependencies

None (consumed optionally by other plugins).
