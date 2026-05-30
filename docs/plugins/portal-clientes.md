# Plugin: Portal Clientes (Customer Portal)

A dedicated Filament panel at `/portal` that allows clients to log in and view their own invoices, quotes, and projects. Uses a separate panel with its own authentication and branding.

## Enable / disable

```dotenv
PLUGIN_PORTAL_CLIENTES_ENABLED=true   # default: true
```

## How it works

1. A client user is created in the admin panel via Users management
2. The user is assigned the `cliente` role
3. The user's account is linked to a `Cliente` record
4. The client can log in at `/portal` and view their own data only

The portal enforces row-level access: a client user can only see invoices, quotes, and projects linked to their own `cliente_id`.

## Dependencies

| Dependency | Effect if missing |
|---|---|
| `presupuestos` plugin enabled | Quotes section hidden from portal |
| `proyectos` plugin enabled | Projects section hidden from portal |

These are conditional: the portal checks with `class_exists()` and `config('plugins.X')` at boot time.

## Routes

| Method | URL | Description |
|---|---|---|
| GET | `/portal/verify-email-change/{token}` | Confirm pending email address change |
| GET | `/portal/facturas/{factura}/pdf` | Download invoice PDF (client-scoped) |
| GET | `/portal/presupuestos/{presupuesto}/pdf` | Download quote PDF (client-scoped) |

## Filament panel

- **Panel ID:** `portal`
- **Path:** `/portal`
- **Authentication:** email + password + password reset (no 2FA required)
- **Middleware:** `EnsureIsCliente` verifies the `cliente` role on every request

## Pages and resources

| Component | Description |
|---|---|
| `Dashboard` | Portal home page |
| `MiPerfil` | Profile page: update name, email (with email verification flow) |
| `FacturaClienteResource` | Read-only invoice list and detail view |
| `PresupuestoClienteResource` | Read-only quote list (loaded if presupuestos enabled) |
| `ProyectoClienteResource` | Read-only project list (loaded if proyectos enabled) |

## Email change flow

Clients can request an email change from their profile. The new address is stored in `email_pending` on the User model and a verification link is sent. On confirmation, the `email` field is updated and `email_pending` is cleared.

## Database migrations

| Migration | Action |
|---|---|
| `add_portal_fields_to_users_table` | Adds `email_pending`, `email_pending_token`, `email_pending_expires_at` to `users` |

## Dependencies

- `presupuestos` plugin (optional — for quote section)
- `proyectos` plugin (optional — for project section)
