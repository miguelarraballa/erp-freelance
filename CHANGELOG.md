# Changelog

All notable changes to this project are documented in this file.

---

## [1.11.0] - 2026-05-30

### Added
- **Plugin system**: runtime enable/disable of plugins from the admin panel (Plugins page), without needing to edit `.env`. State is persisted in `storage/app/plugins_state.json`.
- **Plugin: Servidores** — new plugin for managing servers and domains linked to clients, with renewal alerts (1 month and 15 days in advance). Requires the Notificaciones plugin.
- **Notificaciones**: service renewal notification templates, auto-generated on first run via seeder.
- **API**: new endpoint `POST /api/n8n/servidores/upsert` for syncing server data from n8n automations.
- **Scheduler**: daily cron (`servidores:renovacion-alertas` at 08:00) for sending renewal alerts.

### Changed
- Widgets reorganised: `IngresosGastosMesWidget` moved to the Gastos plugin; `PresupuestosAceptadosTableWidget` and `PresupuestosEmitidosTableWidget` moved to the Presupuestos plugin.

---

## [1.10.0] - 2026-05-28

### Added
- **Plugin: Servidores** (initial release) — manage servers and domains assigned to clients, with renewal date tracking.
- Observations field on servers.
- Configurable column order in the servers list.

---

## [1.9.10] - 2026-05-10

### Added
- Dashboard widget: pending-to-invoice amount shown in the pending quotes widget.

### Fixed
- Presupuestos: several bugs corrected (form validation, status handling).
- Dashboard widgets: only paid invoices count toward the weekly revenue total.
- Dashboard widgets: "accepted" status now shown correctly; all related invoices displayed.

---

## [1.9.8] - 2026-04-02

### Added
- **Plugin: Informes** — custom data source queries (raw SQL) as chart sources.

### Fixed
- Informes: removed a duplicate migration.

---

## [1.9.7] - 2026-03-28

### Added
- **API** — REST endpoints for n8n automations: `POST /api/n8n/email-check` (client email matching).
- Sanctum token authentication for `/api/facturas/{id}/logs`.

### Fixed
- Email format in API responses: `Name <email>` format.
- `api.php` route file not being loaded.
- Factura PDF: minor rendering fix.

---

## [1.9.6] - 2026-03-22

### Added
- **Plugin: Informes** (initial release) — customisable reports and charts powered by Chart.js, with groupable data sources and statistics.
- **Plugin: Anexo RGPD** (initial release) — GDPR data-processing annex with PDF generation.
- Presupuestos: reorderable lines.

### Fixed
- Presupuestos form: order field hidden by default and updated on change.

---

## [1.9.5 and earlier]

See git history for earlier changes.
