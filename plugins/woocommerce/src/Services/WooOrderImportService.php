<?php

namespace Woocommerce\Services;

use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Impuesto;
use App\Services\FacturaLogger;
use App\Models\Pago;
use App\Models\Serie;
use App\Services\SerieNumeracionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Woocommerce\Models\TiendaWoo;
use Woocommerce\Models\WooPedidoImportado;

class WooOrderImportService
{
    // Estados de WooCommerce que se importan como facturas cobradas
    const ESTADOS_COBRADO = ['completed', 'processing'];

    // Estados que se omiten (pendientes de pago, cancelados, etc.)
    const ESTADOS_OMITIR = ['pending', 'cancelled', 'failed', 'trash', 'checkout-draft'];

    public function __construct(private readonly TiendaWoo $tienda) {}

    /**
     * Importa un array de pedidos de WooCommerce para la tienda configurada.
     * Devuelve un resumen: ['importados' => N, 'abonos' => N, 'omitidos' => N, 'errores' => N]
     */
    public function importarPedidos(array $orders): array
    {
        $stats = ['importados' => 0, 'abonos' => 0, 'omitidos' => 0, 'errores' => 0];

        foreach ($orders as $order) {
            try {
                $wooOrderId = (int) $order['id'];
                $wooStatus  = $order['status'] ?? 'unknown';

                // Saltar si ya está importado
                if (WooPedidoImportado::where('tienda_id', $this->tienda->id)
                    ->where('woo_order_id', $wooOrderId)->exists()) {
                    $stats['omitidos']++;
                    continue;
                }

                if (in_array($wooStatus, self::ESTADOS_OMITIR, true)) {
                    $stats['omitidos']++;
                    continue;
                }

                if ($wooStatus === 'refunded') {
                    $this->importarAbono($order);
                    $stats['abonos']++;
                    continue;
                }

                $this->importarFactura($order);
                $stats['importados']++;

            } catch (\Throwable $e) {
                Log::error('[WooCommerce] Error importando pedido', [
                    'tienda'   => $this->tienda->id,
                    'order_id' => $order['id'] ?? null,
                    'error'    => $e->getMessage(),
                ]);
                $stats['errores']++;
            }
        }

        return $stats;
    }

    /**
     * Importa un pedido como factura simplificada cobrada.
     */
    private function importarFactura(array $order): Factura
    {
        return DB::transaction(function () use ($order) {
            $serie = Serie::lockForUpdate()->findOrFail($this->tienda->serie_id);

            $seq           = (int) $serie->siguiente_numero;
            $numeroCompleto = SerieNumeracionService::renderNumeroCompleto($serie, $seq);

            $fecha = $this->parseFecha($order);

            $factura = Factura::create([
                'serie_id'          => $serie->id,
                'numero'            => $seq,
                'numero_completo'   => $numeroCompleto,
                'cliente_id'        => $this->tienda->cliente_id,
                'datos_facturacion' => $this->buildDatosFacturacion($order),
                'fecha'             => $fecha,
                'vencimiento'       => $fecha,
                'estado'            => 'cobrada',
                'tipo'              => 'simplificada',
                'moneda'            => strtolower($order['currency'] ?? 'eur'),
                'notas'             => 'Pedido WooCommerce #' . $order['id'],
                'base'              => 0,
                'iva_total'         => 0,
                'irpf_total'        => 0,
                'total'             => 0,
            ]);

            $serie->increment('siguiente_numero');

            $this->crearLineas($factura, $order['line_items'] ?? [], $order);
            $this->recalcularTotales($factura);
            $this->registrarPago($factura, $order);

            FacturaLogger::log($factura, 'emitida', [
                'origen'          => 'woocommerce',
                'tienda_id'       => $this->tienda->id,
                'woo_order_id'    => $order['id'],
                'numero_completo' => $numeroCompleto,
            ]);

            WooPedidoImportado::create([
                'tienda_id'    => $this->tienda->id,
                'woo_order_id' => (int) $order['id'],
                'factura_id'   => $factura->id,
                'woo_status'   => $order['status'],
            ]);

            return $factura;
        });
    }

    /**
     * Importa un pedido reembolsado como factura de abono.
     */
    private function importarAbono(array $order): Factura
    {
        return DB::transaction(function () use ($order) {
            // Primero importar la factura original si no existe
            $original = WooPedidoImportado::where('tienda_id', $this->tienda->id)
                ->where('woo_order_id', (int) $order['id'])
                ->first();

            if (! $original) {
                // Importar la factura original con estado cobrada antes del abono
                $orderCopia = array_merge($order, ['status' => 'completed']);
                $facturaOriginal = $this->importarFactura($orderCopia);

                // Actualizar el registro de WooPedidoImportado con el estado real
                WooPedidoImportado::where('tienda_id', $this->tienda->id)
                    ->where('woo_order_id', (int) $order['id'])
                    ->update(['woo_status' => 'refunded']);
            } else {
                $facturaOriginal = Factura::findOrFail($original->factura_id);
            }

            // Buscar serie de abono activa para ese ejercicio
            $ejercicio = (int) now()->year;
            $serieAbono = Serie::where('tipo', 'abono')
                ->where('ejercicio', $ejercicio)
                ->where('activo', true)
                ->lockForUpdate()
                ->first();

            // Si no hay serie de abono, usamos la serie de la tienda con prefijo A/
            if (! $serieAbono) {
                $serieAbono = Serie::lockForUpdate()->findOrFail($this->tienda->serie_id);
            }

            $seq            = (int) $serieAbono->siguiente_numero;
            $numeroCompleto = SerieNumeracionService::renderNumeroCompleto($serieAbono, $seq);
            $fecha          = $this->parseFecha($order);

            $abono = Factura::create([
                'serie_id'          => $serieAbono->id,
                'numero'            => $seq,
                'numero_completo'   => $numeroCompleto,
                'cliente_id'        => $this->tienda->cliente_id,
                'datos_facturacion' => $this->buildDatosFacturacion($order),
                'fecha'             => $fecha,
                'vencimiento'       => $fecha,
                'estado'            => 'cobrada',
                'tipo'              => 'abono',
                'rectifica_id'      => $facturaOriginal->id,
                'moneda'            => strtolower($order['currency'] ?? 'eur'),
                'notas'             => 'Reembolso WooCommerce #' . $order['id'],
                'base'              => 0,
                'iva_total'         => 0,
                'irpf_total'        => 0,
                'total'             => 0,
            ]);

            $serieAbono->increment('siguiente_numero');

            // Líneas del abono con importes negativos
            $this->crearLineas($abono, $order['line_items'] ?? [], $order, negativo: true);
            $this->recalcularTotales($abono);

            FacturaLogger::log($abono, 'emitida', [
                'origen'       => 'woocommerce',
                'tienda_id'    => $this->tienda->id,
                'woo_order_id' => $order['id'],
                'tipo'         => 'abono',
            ]);

            WooPedidoImportado::create([
                'tienda_id'    => $this->tienda->id,
                'woo_order_id' => (int) $order['id'] + 1000000, // evitar colisión con la factura original
                'factura_id'   => $abono->id,
                'woo_status'   => 'refunded_abono',
            ]);

            return $abono;
        });
    }

    /**
     * Crea las líneas de factura a partir de los line_items de WooCommerce.
     */
    private function crearLineas(Factura $factura, array $lineItems, array $order, bool $negativo = false): void
    {
        $orden = 1;
        $signo = $negativo ? -1 : 1;

        foreach ($lineItems as $item) {
            $cantidad       = (float) ($item['quantity'] ?? 1);
            $precioUnitario = $signo * round((float) ($item['subtotal'] ?? 0) / max($cantidad, 1), 4);
            $descuento      = 0.0;

            // Calcular descuento si hay diferencia entre subtotal y total
            $subtotal = (float) ($item['subtotal'] ?? 0);
            $total    = (float) ($item['total'] ?? 0);
            if ($subtotal > 0 && $total < $subtotal) {
                $descuento = round((1 - ($total / $subtotal)) * 100, 2);
            }

            // Determinar IVA a partir de los impuestos del item
            $ivaPct   = $this->extractTaxRate($item['taxes'] ?? [], $order['tax_lines'] ?? []);
            $impuesto = $this->resolverImpuesto($ivaPct);

            FacturaLinea::create([
                'factura_id'      => $factura->id,
                'orden'           => $orden++,
                'producto'        => 1, // productos de tienda
                'concepto'        => $item['name'] ?? 'Producto',
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioUnitario,
                'descuento_pct'   => $descuento,
                'impuesto_id'     => $impuesto?->id,
                'base_linea'      => 0,
                'iva_linea'       => 0,
                'irpf_linea'      => 0,
                'total_linea'     => 0,
            ]);
        }

        // Línea adicional por gastos de envío si existen
        $shippingTotal = (float) ($order['shipping_total'] ?? 0);
        if ($shippingTotal > 0) {
            $ivaPct   = $this->extractShippingTaxRate($order['shipping_lines'] ?? [], $order['tax_lines'] ?? []);
            $impuesto = $this->resolverImpuesto($ivaPct);

            FacturaLinea::create([
                'factura_id'      => $factura->id,
                'orden'           => $orden++,
                'producto'        => 1,
                'concepto'        => 'Gastos de envío',
                'cantidad'        => 1,
                'precio_unitario' => $signo * $shippingTotal,
                'descuento_pct'   => 0,
                'impuesto_id'     => $impuesto?->id,
                'base_linea'      => 0,
                'iva_linea'       => 0,
                'irpf_linea'      => 0,
                'total_linea'     => 0,
            ]);
        }
    }

    /**
     * Recalcula los totales de la factura usando FacturaCalc.
     */
    private function recalcularTotales(Factura $factura): void
    {
        $factura->load('lineas.impuesto');
        \App\Services\FacturaCalc::recalcular($factura);
    }

    /**
     * Registra un pago automático para facturas cobradas.
     */
    private function registrarPago(Factura $factura, array $order): void
    {
        $total = (float) ($order['total'] ?? 0);
        if ($total <= 0) {
            return;
        }

        $metodoPago = $order['payment_method_title'] ?? $order['payment_method'] ?? 'WooCommerce';
        $fecha      = $this->parseFecha($order);

        Pago::create([
            'factura_id'  => $factura->id,
            'importe'     => $total,
            'fecha_pago'  => $fecha,
            'metodo'      => $metodoPago,
            'notas'       => 'Importado automáticamente desde WooCommerce',
        ]);
    }

    /**
     * Construye el texto de datos de facturación a partir de la dirección de billing de WooCommerce.
     */
    private function buildDatosFacturacion(array $order): string
    {
        $b = $order['billing'] ?? [];

        $nombre   = trim(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? ''));
        $empresa  = $b['company'] ?? '';
        $dir      = $b['address_1'] ?? '';
        $dir2     = $b['address_2'] ?? '';
        $ciudad   = $b['city'] ?? '';
        $cp       = $b['postcode'] ?? '';
        $pais     = $b['country'] ?? '';
        $email    = $b['email'] ?? '';
        $telefono = $b['phone'] ?? '';

        return implode("\n", array_filter([
            $empresa ?: $nombre,
            $empresa ? $nombre : null,
            $dir,
            $dir2,
            trim("$cp $ciudad"),
            $pais,
            $email,
            $telefono,
        ]));
    }

    /**
     * Extrae el porcentaje de IVA de los taxes de una línea.
     */
    private function extractTaxRate(array $lineTaxes, array $taxLines): float
    {
        foreach ($lineTaxes as $tax) {
            $taxId = $tax['id'] ?? null;
            foreach ($taxLines as $tl) {
                if (($tl['id'] ?? null) == $taxId) {
                    return (float) ($tl['rate_percent'] ?? 0);
                }
            }
        }

        // Fallback: si solo hay una tax_line, usar esa
        if (count($taxLines) === 1) {
            return (float) ($taxLines[0]['rate_percent'] ?? 0);
        }

        return 0.0;
    }

    /**
     * Extrae el IVA de la línea de envío.
     */
    private function extractShippingTaxRate(array $shippingLines, array $taxLines): float
    {
        foreach ($shippingLines as $sl) {
            $taxes = $sl['taxes'] ?? [];
            return $this->extractTaxRate($taxes, $taxLines);
        }
        return 0.0;
    }

    /**
     * Resuelve el impuesto del sistema más cercano al porcentaje dado.
     */
    private function resolverImpuesto(float $pct): ?Impuesto
    {
        if ($pct <= 0) {
            return null;
        }

        // Buscar IVA exacto
        $impuesto = Impuesto::where('tipo', 'iva')
            ->where('activo', 1)
            ->where('porcentaje', $pct)
            ->first();

        if ($impuesto) {
            return $impuesto;
        }

        // Más cercano
        return Impuesto::where('tipo', 'iva')
            ->where('activo', 1)
            ->orderByRaw('ABS(porcentaje - ?)', [$pct])
            ->first();
    }

    /**
     * Extrae la fecha del pedido (date_paid o date_created).
     */
    private function parseFecha(array $order): string
    {
        $fecha = $order['date_paid'] ?? $order['date_completed'] ?? $order['date_created'] ?? null;

        if ($fecha) {
            return substr($fecha, 0, 10); // YYYY-MM-DD
        }

        return now()->toDateString();
    }
}
