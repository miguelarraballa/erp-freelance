<?php

namespace Woocommerce\Services;

use Illuminate\Support\Facades\Http;
use Woocommerce\Models\TiendaWoo;

class WoocommerceApiService
{
    private TiendaWoo $tienda;

    public function __construct(TiendaWoo $tienda)
    {
        $this->tienda = $tienda;
    }

    /**
     * Obtiene pedidos desde WooCommerce.
     *
     * @param  string|null  $after  Fecha ISO8601; si se pasa, solo pedidos creados después
     * @param  int          $page   Página (1-indexed)
     * @param  int          $perPage
     * @return array
     */
    public function getOrders(?string $after = null, int $page = 1, int $perPage = 100): array
    {
        $params = [
            'per_page' => $perPage,
            'page'     => $page,
            'orderby'  => 'date',
            'order'    => 'asc',
            'status'   => 'any',
        ];

        if ($after) {
            $params['after'] = $after;
        }

        $response = Http::withBasicAuth($this->tienda->consumer_key, $this->tienda->consumer_secret)
            ->get(rtrim($this->tienda->url, '/') . '/wp-json/wc/v3/orders', $params);

        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * Obtiene todos los pedidos desde la última sincronización, paginando automáticamente.
     */
    public function getAllOrdersSinceLastSync(): array
    {
        $after = $this->tienda->ultima_sincronizacion
            ? $this->tienda->ultima_sincronizacion->toIso8601String()
            : null;

        $allOrders = [];
        $page = 1;

        do {
            $batch = $this->getOrders(after: $after, page: $page);
            $allOrders = array_merge($allOrders, $batch);
            $page++;
        } while (count($batch) === 100);

        return $allOrders;
    }

    /**
     * Obtiene los reembolsos de un pedido.
     */
    public function getOrderRefunds(int $orderId): array
    {
        $response = Http::withBasicAuth($this->tienda->consumer_key, $this->tienda->consumer_secret)
            ->get(rtrim($this->tienda->url, '/') . "/wp-json/wc/v3/orders/{$orderId}/refunds");

        $response->throw();

        return $response->json() ?? [];
    }
}
