<?php

/*
|--------------------------------------------------------------------------
| Plugin Enable/Disable Flags
|--------------------------------------------------------------------------
|
| Set each flag to true or false to enable or disable a plugin.
| You can also control them via environment variables in your .env file,
| or at runtime via the Plugins page in the admin panel (which writes
| to storage/app/plugins_state.json and takes precedence over .env).
|
| When a plugin is disabled, its routes, migrations and Filament UI
| components will not be loaded. Already-run migrations are not rolled back.
|
| Plugin dependencies:
|   - portal-clientes: shows quotes section only if presupuestos is enabled
|   - portal-clientes: shows projects section only if proyectos is enabled
|   - All plugins: email notifications require notificaciones to be enabled
|   - servidores: renewal alerts require notificaciones to be enabled
|
*/

$stateFile = dirname(__DIR__) . '/storage/app/plugins_state.json';
$state = [];
if (is_file($stateFile)) {
    $state = json_decode(file_get_contents($stateFile), true) ?? [];
}

$get = fn(string $key, bool $default): bool =>
    array_key_exists($key, $state)
        ? (bool) $state[$key]
        : (bool) env('PLUGIN_' . strtoupper(str_replace('-', '_', $key)) . '_ENABLED', $default);

return [
    'gastos'          => $get('gastos', true),
    'presupuestos'    => $get('presupuestos', true),
    'proyectos'       => $get('proyectos', true),
    'notificaciones'  => $get('notificaciones', true),
    'woocommerce'     => $get('woocommerce', false),
    'informes'        => $get('informes', true),
    'anexo-rgpd'      => $get('anexo-rgpd', true),
    'portal-clientes' => $get('portal-clientes', true),
    'servidores'      => $get('servidores', true),
];
