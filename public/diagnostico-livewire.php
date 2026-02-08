<?php
/**
 * Diagnóstico de Livewire - Verifica configuración que puede causar CorruptComponentPayloadException
 */
declare(strict_types=1);

$base = realpath(__DIR__ . '/..');
header('Content-Type: text/html; charset=utf-8');

require $base . '/vendor/autoload.php';

// Cargar .env
if (file_exists($base.'/.env')) {
    Dotenv\Dotenv::createImmutable($base)->safeLoad();
}

// Bootstrap Laravel
$app = require $base . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Livewire</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f7fafc;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #4299e1;
        }
        .section h2 {
            color: #2d3748;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #4a5568;
        }
        .info-value {
            color: #2d3748;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-ok {
            background: #c6f6d5;
            color: #22543d;
        }
        .status-warning {
            background: #fef5e7;
            color: #744210;
        }
        .status-error {
            background: #fed7d7;
            color: #742a2a;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-warning {
            background: #fef5e7;
            color: #744210;
            border-left: 4px solid #f39c12;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        code {
            background: #2d3748;
            color: #48bb78;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Livewire</h1>
        <p class="subtitle">Verifica la configuración que puede causar CorruptComponentPayloadException</p>

        <?php
        $issues = [];

        // 1. APP_KEY
        $appKey = config('app.key');
        $appKeyStatus = $appKey ? 'ok' : 'error';
        if (!$appKey) {
            $issues[] = 'APP_KEY no está configurada';
        }
        ?>

        <div class="section">
            <h2>🔑 Configuración de Aplicación</h2>
            <div class="info-row">
                <span class="info-label">APP_KEY</span>
                <span class="info-value">
                    <?php if ($appKey): ?>
                        <?= substr($appKey, 0, 20) ?>...
                        <span class="status status-ok">Configurada</span>
                    <?php else: ?>
                        <span class="status status-error">No configurada</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">APP_ENV</span>
                <span class="info-value">
                    <?= config('app.env') ?>
                    <?php if (config('app.env') === 'production'): ?>
                        <span class="status status-ok">Producción</span>
                    <?php else: ?>
                        <span class="status status-warning">Desarrollo</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">APP_DEBUG</span>
                <span class="info-value">
                    <?= config('app.debug') ? 'true' : 'false' ?>
                    <?php if (!config('app.debug')): ?>
                        <span class="status status-ok">Deshabilitado</span>
                    <?php else: ?>
                        <span class="status status-warning">Habilitado</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <?php
        // 2. Cachés
        $cacheDriver = config('cache.default');
        $configCached = file_exists($base . '/bootstrap/cache/config.php');
        $routesCached = file_exists($base . '/bootstrap/cache/routes-v7.php');
        ?>

        <div class="section">
            <h2>💾 Sistema de Caché</h2>
            <div class="info-row">
                <span class="info-label">Driver de Caché</span>
                <span class="info-value"><?= $cacheDriver ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Config Cacheada</span>
                <span class="info-value">
                    <?php if ($configCached): ?>
                        <span class="status status-warning">Sí</span>
                    <?php else: ?>
                        <span class="status status-ok">No</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Routes Cacheadas</span>
                <span class="info-value">
                    <?php if ($routesCached): ?>
                        <span class="status status-warning">Sí</span>
                    <?php else: ?>
                        <span class="status status-ok">No</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <?php
        // 3. Livewire
        $livewireAssetUrl = config('livewire.asset_url');
        $livewireManifest = config('livewire.manifest_path');
        ?>

        <div class="section">
            <h2>⚡ Configuración de Livewire</h2>
            <div class="info-row">
                <span class="info-label">Asset URL</span>
                <span class="info-value"><?= $livewireAssetUrl ?: 'Default' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Versión</span>
                <span class="info-value">
                    <?php
                    try {
                        $version = \Composer\InstalledVersions::getVersion('livewire/livewire') ?? 'Desconocida';
                        echo $version;
                    } catch (Exception $e) {
                        echo 'Desconocida';
                    }
                    ?>
                </span>
            </div>
        </div>

        <?php
        // 4. PHP y servidor
        $phpVersion = PHP_VERSION;
        $mbstringLoaded = extension_loaded('mbstring');
        $jsonLoaded = extension_loaded('json');
        ?>

        <div class="section">
            <h2>🐘 Entorno PHP</h2>
            <div class="info-row">
                <span class="info-label">Versión PHP</span>
                <span class="info-value">
                    <?= $phpVersion ?>
                    <?php if (version_compare($phpVersion, '8.1.0', '>=')): ?>
                        <span class="status status-ok">Compatible</span>
                    <?php else: ?>
                        <span class="status status-warning">Actualizar recomendado</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Extensión mbstring</span>
                <span class="info-value">
                    <?php if ($mbstringLoaded): ?>
                        <span class="status status-ok">Cargada</span>
                    <?php else: ?>
                        <span class="status status-error">No cargada</span>
                        <?php $issues[] = 'Extensión mbstring no está cargada'; ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Extensión json</span>
                <span class="info-value">
                    <?php if ($jsonLoaded): ?>
                        <span class="status status-ok">Cargada</span>
                    <?php else: ?>
                        <span class="status status-error">No cargada</span>
                        <?php $issues[] = 'Extensión json no está cargada'; ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <?php if ($configCached || $routesCached): ?>
        <div class="alert alert-warning">
            <strong>⚠️ Cachés Activas Detectadas</strong><br>
            Tienes cachés activas. Si acabas de hacer cambios en el código, necesitas limpiarlas usando
            <a href="/clear-cache.html" style="color: #744210; font-weight: 600;">clear-cache.html</a>
        </div>
        <?php endif; ?>

        <?php if (count($issues) > 0): ?>
        <div class="alert alert-warning">
            <strong>⚠️ Problemas Encontrados:</strong>
            <ul style="margin: 10px 0 0 20px;">
                <?php foreach ($issues as $issue): ?>
                    <li><?= htmlspecialchars($issue) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <strong>✅ No se encontraron problemas de configuración</strong><br>
            Si sigues teniendo el error <code>CorruptComponentPayloadException</code>, el problema está en las
            validaciones de los campos del formulario. Asegúrate de que todos los campos que pueden ser negativos
            tengan <code>->rules(['numeric'])</code> en lugar de solo <code>->numeric()</code>.
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0; text-align: center; color: #718096; font-size: 14px;">
            Diagnóstico generado: <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</body>
</html>
