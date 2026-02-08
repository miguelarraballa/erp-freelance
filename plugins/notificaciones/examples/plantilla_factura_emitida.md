# Plantilla: Factura Emitida

Esta es la plantilla que debe crearse en el panel de Filament para que el sistema de notificaciones automáticas funcione.

## Crear la Plantilla

Desde el panel de Filament:

1. Ve a **Empresa > Plantillas de Notificaciones**
2. Click en **Crear**
3. Rellena los campos:

### Datos de la Plantilla

**Nombre:** `factura_emitida`
*(Este nombre debe coincidir exactamente con el usado en el código)*

**Asunto:**
```
Nueva factura {{facturas_numero_completo}} de {{emisores_nombre}}
```

**Cuerpo HTML:**
```html
<p>Estimado/a {{clientes_contacto_nombre}},</p>

<p>Le informamos que se ha generado una nueva factura:</p>

<ul>
    <li><strong>Número:</strong> {{facturas_numero_completo}}</li>
    <li><strong>Fecha:</strong> {{facturas_fecha}}</li>
    <li><strong>Importe total:</strong> {{facturas_total}}€</li>
    <li><strong>Base imponible:</strong> {{facturas_base}}€</li>
    <li><strong>IVA:</strong> {{facturas_iva_total}}€</li>
</ul>

<p>Puede descargar la factura adjunta en este correo.</p>

<p>Atentamente,<br>
{{emisores_nombre}}</p>
```

**HTML Personalizado (Pie de página):**
```html
<hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">
<small style="color: #666;">
Este es un correo electrónico automático, por favor no responder a esta dirección.<br>
Para cualquier consulta, contacte con nosotros en {{emisores_email}} o llámenos al {{emisores_telefono}}.
</small>
```

**Cuerpo texto (opcional):**
```
Estimado/a {{clientes_contacto_nombre}},

Le informamos que se ha generado una nueva factura:

- Número: {{facturas_numero_completo}}
- Fecha: {{facturas_fecha}}
- Importe total: {{facturas_total}}€
- Base imponible: {{facturas_base}}€
- IVA: {{facturas_iva_total}}€

Puede descargar la factura adjunta en este correo.

Atentamente,
{{emisores_nombre}}

---
Este es un correo electrónico automático, por favor no responder a esta dirección.
Para cualquier consulta, contacte con nosotros en {{emisores_email}} o llámenos al {{emisores_telefono}}.
```

## Etiquetas Necesarias

Asegúrate de que existen estas etiquetas en **Empresa > Etiquetas**:

| Etiqueta | Valor |
|----------|-------|
| `facturas_numero_completo` | `facturas.numero_completo` |
| `facturas_fecha` | `facturas.fecha` |
| `facturas_total` | `facturas.total` |
| `facturas_base` | `facturas.base` |
| `facturas_iva_total` | `facturas.iva_total` |
| `clientes_contacto_nombre` | `clientes.contacto_nombre` |
| `emisores_nombre` | `emisores.nombre` |
| `emisores_email` | `emisores.email` |
| `emisores_telefono` | `emisores.telefono` |

Puedes crear estas etiquetas manualmente o ejecutar el seeder:

```bash
php artisan db:seed --class="Notificaciones\Database\Seeders\NotificacionesEtiquetasSeeder"
```

## Cómo Funciona

1. **Usuario emite una factura** desde el panel (cambia estado de "borrador" a "emitida")
2. **El sistema detecta el cambio** en `EditFactura::afterSave()`
3. **Se genera la factura** con número completo
4. **Se encola automáticamente** el email usando la plantilla "factura_emitida"
5. **El cronjob envía el email** (ejecutar cada 5 minutos):
   ```bash
   php artisan notificaciones:enviar
   ```

## Resultado

El cliente recibirá un email con:
- ✅ Asunto personalizado con el número de factura
- ✅ Contenido HTML procesado con todos los datos
- ✅ PDF de la factura adjunto
- ✅ Pie de página con aviso legal

## Verificar que Funciona

1. **Crear plantilla** "factura_emitida" en Filament
2. **Emitir una factura** de prueba
3. **Ver en Notificaciones** que se creó un registro con estado "en_cola"
4. **Ejecutar comando** manualmente:
   ```bash
   php artisan notificaciones:enviar --debug
   ```
5. **Verificar email** enviado

## Troubleshooting

### No se crea la notificación
- Verificar que el cliente tiene email válido
- Verificar que la plantilla se llama exactamente "factura_emitida"
- Revisar los logs en `storage/logs/laravel.log`

### El email no se envía
- Verificar configuración SMTP en `.env`
- Ejecutar el comando con `--debug` para ver detalles
- Revisar el campo `error` en la tabla notificaciones

### Las etiquetas no se reemplazan
- Verificar que las etiquetas existen en la tabla `notificaciones_etiquetas`
- Verificar que el formato es correcto: `tabla.campo`
- Ejecutar el seeder para crear las etiquetas automáticamente
