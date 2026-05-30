# Plugin: WooCommerce Integration

Automatically imports completed orders from one or more WooCommerce stores and creates invoices for them. Tracks which orders have already been imported to prevent duplicates.

## Enable / disable

This plugin is **disabled by default** because it requires external API credentials.

```dotenv
PLUGIN_WOOCOMMERCE_ENABLED=false   # default: false
```

Set to `true` and configure at least one store via the admin panel to use this plugin.

## Setup

1. Enable the plugin: `PLUGIN_WOOCOMMERCE_ENABLED=true`
2. Run `php artisan migrate` to create the plugin tables
3. In the admin panel, go to **WooCommerce → Stores** and add your store:
   - URL: your WooCommerce site URL
   - Consumer key / Consumer secret: generated in WooCommerce → Settings → Advanced → REST API

## Models

### `Woocommerce\Models\TiendaWoo`

| Field | Description |
|---|---|
| `nombre` | Store display name |
| `url` | WooCommerce site URL |
| `consumer_key` | WooCommerce REST API key |
| `consumer_secret` | WooCommerce REST API secret |
| `serie_id` | Default invoice series for imported orders |
| `cliente_id` | Default client assigned to imported invoices |
| `ultima_sincronizacion` | Timestamp of the last successful import run |
| `activo` | Whether this store is active for import |

### `Woocommerce\Models\WooPedidoImportado`

Pivot table linking a WooCommerce order ID to the generated local invoice, preventing duplicate imports.

| Field | Description |
|---|---|
| `tienda_id` | FK to TiendaWoo |
| `woo_order_id` | Remote WooCommerce order ID |
| `factura_id` | FK to the generated Factura |
| `woo_status` | WooCommerce order status at import time |

## Artisan commands

```bash
php artisan woo:importar-pedidos
```

Fetches all `completed` orders from active stores that have not yet been imported and creates invoices for them. Safe to run repeatedly (idempotent).

## Database migrations

| Migration | Creates table |
|---|---|
| `create_tiendas_woo_table` | `tiendas_woo` |
| `create_woo_pedidos_importados_table` | `woo_pedidos_importados` |

## Dependencies

None.
