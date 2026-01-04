<?php
/**
 * Artisan Bridge (cPanel, sin SSH)
 * - Colócalo junto a tu index público (public/ o public_html/).
 * - Protegido por token .env(ARTISAN_BRIDGE_TOKEN).
 * - SOLO ejecuta comandos en la whitelist.
 */

declare(strict_types=1);

// ========= CONFIG BÁSICA =========
// Ajusta si tu public_html NO está dentro del proyecto:
$base = realpath(__DIR__ . '/..');            // p.ej. /home/USER/laravel-app
// $base = '/home/USER/laravel-app';          // <-- si lo necesitas, usa ruta absoluta

header('Content-Type: application/json; charset=utf-8');

// Requiere POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Use POST']); exit;
}

// Bootstrap Laravel sin pasar por el kernel HTTP
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Carga env y verifica token
$tokenProvided = $_POST['token'] ?? $_SERVER['HTTP_X_BRIDGE_TOKEN'] ?? null;
$tokenExpected = env('ARTISAN_BRIDGE_TOKEN'); // definido en .env de producción

if (!$tokenExpected || !hash_equals((string)$tokenExpected, (string)$tokenProvided)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']); exit;
}

// ========== WHITELIST de comandos ==========
$allowed = [
    // Caches / optimizaciones
    'optimize:clear'                  => [],
    'config:clear'                    => [],
    'config:cache'                    => [],
    'route:clear'                     => [],
    'route:cache'                     => [],
    'view:clear'                      => [],
    // Filament
    'filament:clear-cached-components'=> [],
    'filament:cache-components'       => [],
    // Storage symlink (puede fallar en hostings)
    'storage:link'                    => [],
    // Migraciones (SIEMPRE con --force)
    'migrate'                         => ['--force' => true],
];

// Entrada: cmd = nombre exacto (clave del array anterior)
$cmd = (string)($_POST['cmd'] ?? '');
if (!array_key_exists($cmd, $allowed)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Comando no permitido']); exit;
}

// Seguridad extra para migrate: requiere confirm explícita
if ($cmd === 'migrate' && (($_POST['confirm'] ?? '') !== 'YES')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Para migrate añade confirm=YES']); exit;
}

try {
    $params  = $allowed[$cmd];
    $exit    = $kernel->call($cmd, $params);
    $output  = $kernel->output();

    echo json_encode([
        'ok'     => $exit === 0,
        'exit'   => $exit,
        'cmd'    => $cmd,
        'output' => $output,
        'time'   => date('c'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}