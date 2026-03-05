<?php

namespace Woocommerce\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Woocommerce\Models\TiendaWoo;
use Woocommerce\Services\WoocommerceApiService;
use Woocommerce\Services\WooOrderImportService;

class ImportarPedidosWooCommand extends Command
{
    protected $signature = 'woo:import
                            {--tienda= : ID de la tienda a importar (omitir para todas)}
                            {--debug  : Mostrar información detallada}';

    protected $description = 'Importa pedidos de WooCommerce como facturas simplificadas';

    public function handle(): int
    {
        $debug    = $this->option('debug');
        $tiendaId = $this->option('tienda');

        $query = TiendaWoo::where('activo', true)->with(['serie', 'cliente']);

        if ($tiendaId) {
            $query->where('id', (int) $tiendaId);
        }

        $tiendas = $query->get();

        if ($tiendas->isEmpty()) {
            $this->warn('No hay tiendas activas configuradas.');
            return self::SUCCESS;
        }

        $totalImportados = 0;
        $totalAbonos     = 0;
        $totalErrores    = 0;

        foreach ($tiendas as $tienda) {
            if ($debug) {
                $this->info("▶ Procesando tienda: {$tienda->nombre} ({$tienda->url})");
            }

            try {
                $api     = new WoocommerceApiService($tienda);
                $orders  = $api->getAllOrdersSinceLastSync();

                if (empty($orders)) {
                    if ($debug) {
                        $this->line('  Sin pedidos nuevos.');
                    }
                    $tienda->update(['ultima_sincronizacion' => now()]);
                    continue;
                }

                if ($debug) {
                    $this->line('  Pedidos encontrados: ' . count($orders));
                }

                $importer = new WooOrderImportService($tienda);
                $stats    = $importer->importarPedidos($orders);

                $tienda->update(['ultima_sincronizacion' => now()]);

                $totalImportados += $stats['importados'];
                $totalAbonos     += $stats['abonos'];
                $totalErrores    += $stats['errores'];

                $this->info(sprintf(
                    '  ✓ %s → %d importadas, %d abonos, %d omitidas, %d errores',
                    $tienda->nombre,
                    $stats['importados'],
                    $stats['abonos'],
                    $stats['omitidos'],
                    $stats['errores'],
                ));

            } catch (\Throwable $e) {
                $this->error("  ✗ Error en {$tienda->nombre}: {$e->getMessage()}");
                Log::error('[WooCommerce] Error al procesar tienda', [
                    'tienda_id' => $tienda->id,
                    'error'     => $e->getMessage(),
                ]);
                $totalErrores++;
            }
        }

        $this->info(sprintf(
            'Completado: %d facturas, %d abonos, %d errores',
            $totalImportados,
            $totalAbonos,
            $totalErrores,
        ));

        return $totalErrores > 0 ? self::FAILURE : self::SUCCESS;
    }
}
