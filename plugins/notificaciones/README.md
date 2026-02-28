# Plugin de Notificaciones

Sistema de gestión de notificaciones por email con plantillas, etiquetas dinámicas y cola de envío.

## Características

- **Plantillas de email** con soporte para HTML y texto plano
- **Sistema de etiquetas** dinámicas que obtienen valores de la base de datos
- **Cola de envío** asíncrona mediante cronjob
- **Adjuntos** automáticos de facturas o presupuestos
- **Seguimiento** de estado de envío (en_cola, enviado, error)

## Uso Básico

### 1. Crear una plantilla

Desde el panel de administración de Filament, ve a **Empresa > Plantillas de Notificaciones** y crea una nueva plantilla con:

- **Nombre**: identificador de la plantilla (ej: "factura_enviada")
- **Asunto**: asunto del email con etiquetas
- **Cuerpo HTML**: contenido HTML del email
- **HTML Personalizado**: pie de página con avisos legales (opcional)
- **Cuerpo texto**: versión texto plano (opcional)

Ejemplo de plantilla:
```
Nombre: factura_enviada
Asunto: Factura {{facturas_numero}} de {{emisores_nombre}}

Cuerpo HTML:
<p>Estimado/a {{clientes_contacto_nombre}},</p>
<p>Adjuntamos la factura <strong>{{facturas_numero}}</strong> por un importe de {{facturas_total}}€.</p>
<p>Gracias por su confianza.</p>

HTML Personalizado:
<small>Este es un mensaje automático. Por favor no responder.</small>
```

### 2. Crear etiquetas

Ve a **Empresa > Etiquetas** y crea las etiquetas necesarias:

- `facturas_numero` → `facturas.numero`
- `facturas_total` → `facturas.total`
- `clientes_contacto_nombre` → `clientes.contacto_nombre`
- `emisores_nombre` → `emisores.nombre`

O ejecuta el seeder para crear automáticamente todas las etiquetas:

```bash
php artisan db:seed --class="Notificaciones\Database\Seeders\NotificacionesEtiquetasSeeder"
```

### 3. Encolar un email desde código

```php
use Notificaciones\Helpers\NotificacionesHelper;

// Ejemplo: Enviar factura a un cliente
$factura = Factura::find(1);

NotificacionesHelper::queueEmail(
    plantillaNombre: 'factura_enviada',
    emailDestinatario: $factura->cliente->email,
    context: [
        'facturas' => $factura->id,
        'clientes' => $factura->cliente_id,
        'emisores' => $factura->emisor_id,
    ],
    relacionadoTabla: 'facturas',
    relacionadoId: $factura->id,
    adjuntable: $factura  // Adjuntará el PDF de la factura
);
```

### 4. Configurar el cronjob

Añade el siguiente comando al crontab del servidor:

```bash
# Ejecutar cada 5 minutos
*/5 * * * * cd /home/contabilidad/public_html/app && php artisan notificaciones:enviar >> /dev/null 2>&1
```

O en el archivo `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('notificaciones:enviar')->everyFiveMinutes();
}
```

## API del Helper

### `NotificacionesHelper::queueEmail()`

Encola un email para envío.

**Parámetros:**
- `plantillaNombre` (string): Nombre de la plantilla a usar
- `emailDestinatario` (string): Email del destinatario
- `context` (array): Array con tabla => id para reemplazar etiquetas
- `relacionadoTabla` (string|null): Tabla relacionada (para tracking)
- `relacionadoId` (int|null): ID del registro relacionado
- `adjuntable` (Model|null): Modelo a adjuntar (Factura o Presupuesto)

**Retorna:** `Notificacion` - El registro de notificación creado

**Excepciones:**
- `InvalidArgumentException` si el email no es válido
- `InvalidArgumentException` si la plantilla no existe

### `NotificacionesHelper::replaceTags()`

Reemplaza etiquetas en un texto.

**Parámetros:**
- `text` (string): Texto con etiquetas en formato `{{tag_name}}`
- `context` (array|null): Array con tabla => id para obtener valores

**Retorna:** `string` - Texto con etiquetas reemplazadas

### `NotificacionesHelper::getTagValue()`

Obtiene el valor de una etiqueta específica.

**Parámetros:**
- `tagName` (string): Nombre de la etiqueta
- `context` (array|null): Array con tabla => id
- `id` (int|null): ID específico a usar (opcional)

**Retorna:** `mixed` - Valor de la etiqueta o null

## Comando Artisan

### `notificaciones:enviar`

Envía los emails en cola.

```bash
# Enviar hasta 50 emails (por defecto)
php artisan notificaciones:enviar

# Enviar hasta 100 emails
php artisan notificaciones:enviar --limit=100

# Modo debug (muestra información detallada)
php artisan notificaciones:enviar --debug
```

## Estados de Notificación

- **en_cola**: Email pendiente de envío
- **enviado**: Email enviado exitosamente
- **error**: Error al enviar (revisa el campo `error` para detalles)

## Adjuntos

Para que los adjuntos funcionen, el modelo (Factura o Presupuesto) debe tener uno de:

1. Un método `generarPdf()` que retorne la ruta del PDF
2. Un atributo `pdf_path` con la ruta del archivo

Ejemplo:

```php
class Factura extends Model
{
    public function generarPdf(): string
    {
        // Lógica para generar PDF
        return storage_path("app/facturas/factura-{$this->id}.pdf");
    }
}
```

## Ejemplos Avanzados

### Enviar presupuesto con múltiples contextos

```php
$presupuesto = Presupuesto::find(1);

NotificacionesHelper::queueEmail(
    plantillaNombre: 'presupuesto_nuevo',
    emailDestinatario: $presupuesto->cliente->email,
    context: [
        'presupuestos' => $presupuesto->id,
        'clientes' => $presupuesto->cliente_id,
        'proyectos' => $presupuesto->proyecto_id,
    ],
    adjuntable: $presupuesto
);
```

### Enviar notificación sin adjuntos

```php
NotificacionesHelper::queueEmail(
    plantillaNombre: 'recordatorio_pago',
    emailDestinatario: 'cliente@ejemplo.com',
    context: [
        'facturas' => 123,
        'clientes' => 45,
    ]
);
```

### Procesar etiquetas en texto personalizado

```php
$texto = "Hola {{clientes_contacto_nombre}}, tu factura {{facturas_numero}} está lista.";

$textoFinal = NotificacionesHelper::replaceTags($texto, [
    'clientes' => 45,
    'facturas' => 123,
]);

echo $textoFinal;
// Output: "Hola Juan Pérez, tu factura 2024/001 está lista."
```

## Configuración de Email

Asegúrate de configurar correctamente el driver de email en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.ejemplo.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@ejemplo.com
MAIL_PASSWORD=tu-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ejemplo.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Monitoreo y Logs

Los errores de envío se registran en:
- **Base de datos**: Campo `error` en la tabla `notificaciones`
- **Log de Laravel**: `storage/logs/laravel.log`

Para ver notificaciones con error:

```php
$notificacionesError = Notificacion::where('estado', NotificacionEstado::Error)->get();

foreach ($notificacionesError as $notif) {
    echo "Error en {$notif->email_destinatario}: {$notif->error}\n";
}
```
