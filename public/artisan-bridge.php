<?php
/**
 * Artisan Bridge (cPanel, sin SSH)
 * Protegido por token (.env: ARTISAN_BRIDGE_TOKEN).
 * SOLO ejecuta comandos whitelisteados.
 */
declare(strict_types=1);

$base = realpath(__DIR__ . '/..'); // /home/USER/erp
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Use POST']); exit;
}

require $base . '/vendor/autoload.php';

/** Cargar .env a $_ENV/$_SERVER aunque exista config:cache */
if (file_exists($base.'/.env')) {
    Dotenv\Dotenv::createImmutable($base)->safeLoad();
}

/** Token esperado: leer de superglobales, no usar config()/env() aqu��� */
$tokenExpected = $_ENV['ARTISAN_BRIDGE_TOKEN']
    ?? $_SERVER['ARTISAN_BRIDGE_TOKEN']
    ?? null;

/** Token recibido por POST o cabecera */
$tokenProvided = $_POST['token'] ?? ($_SERVER['HTTP_X_BRIDGE_TOKEN'] ?? null);

if (!$tokenExpected || !hash_equals((string)$tokenExpected, (string)$tokenProvided)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']); exit;
}

/** Bootstrap de la app y kernel de consola */
$app = require $base . '/bootstrap/app.php';
/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

/** Whitelist de comandos */
$allowed = [
    'optimize:clear' => [],
    'config:clear'   => [],
    'config:cache'   => [],
    'route:clear'    => [],
    'route:cache'    => [],
    'view:clear'     => [],
    'filament:clear-cached-components' => [],
    'filament:cache-components'        => [],
    'storage:link'   => [],
    'migrate'        => ['--force' => true],
     'db:seed'       => ['--force' => true],
];

$cmd = (string)($_POST['cmd'] ?? '');
if (!array_key_exists($cmd, $allowed)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Comando no permitido']); exit;
}

if ($cmd === 'migrate' && (($_POST['confirm'] ?? '') !== 'YES')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Para migrate a���ade confirm=YES']); exit;
}

try {
    
    $params = $allowed[$cmd];

    // Permitir clase del seeder (whitelist para seguridad)
    if ($cmd === 'db:seed') {
        $class = trim((string)($_POST['class'] ?? ''));
        if ($class !== '') {
            $whitelist = [
                'Database\\Seeders\\DatabaseSeeder',
                'Database\\Seeders\\ImpuestosSeeder',
                'Database\\Seeders\\SeriesSeeder',
                // a���ade aqu��� tus seeders permitidos
            ];
            if (! in_array($class, $whitelist, true)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Seeder no permitido']); exit;
            }
            $params['--class'] = $class;
        }
    }
        
    
    $exit   = $kernel->call($cmd, $allowed[$cmd]);
    $output = $kernel->output();

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