# Plugin: Anexo RGPD (GDPR Data Processing Annex)

Generates and stores GDPR data-processing agreements (Anexo de Encargado del Tratamiento) between the business and its clients. Produces a signed PDF document listing the systems the client grants access to.

## Enable / disable

```dotenv
PLUGIN_ANEXO_RGPD_ENABLED=true   # default: true
```

## Model

### `AnexoRgpd\Models\AnexoRgpd`

Client contact data is stored **denormalized** (snapshot) so the document remains legally valid even if the client record changes later.

| Field | Description |
|---|---|
| `cliente_id` | FK to Cliente (for UI navigation) |
| `cliente_nombre` | Snapshot: client name |
| `cliente_nif` | Snapshot: client tax ID |
| `cliente_direccion` | Snapshot: client address |
| `cliente_email` | Snapshot: client email |
| `cliente_firmante` | Signing person's full name |
| `cliente_cargo` | Signing person's job title |
| `descripcion_servicio` | Description of the service provided |
| `fecha_inicio` | Agreement start date |
| `duracion_acceso` | Duration of access (free text) |
| `accesos` | JSON array of system keys (see below) |
| `accesos_otros` | Free text for additional accesses |
| `observaciones` | Notes |

### System access keys (`accesos` array)

| Key | Label |
|---|---|
| `wordpress` | WordPress |
| `hosting` | Hosting / cPanel |
| `base_datos` | Database |
| `woocommerce` | WooCommerce |
| `formularios` | Forms |
| `correo` | Email |
| `tpv` | Payment terminal / RedSys |
| `analytics` | Analytics / Search Console / Tag Manager |
| `otros` | Others |

## Routes

| Method | URL | Description |
|---|---|---|
| GET | `/anexos-rgpd/{anexoRgpd}/pdf` | Download the annex as a PDF |

## Filament resources

- **AnexoRgpdResource** — CRUD for GDPR annexes, with PDF download action

## Database migrations

| Migration | Creates table |
|---|---|
| `create_anexos_rgpd_table` | `anexos_rgpd` |

## Dependencies

None.
