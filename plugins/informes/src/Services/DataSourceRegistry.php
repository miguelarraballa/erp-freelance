<?php

namespace Informes\Services;

/**
 * Registro de fuentes de datos disponibles para las gráficas.
 *
 * Los modelos del core de la app siempre están disponibles.
 * Los modelos de plugins se comprueban con class_exists() antes de registrarse,
 * para evitar errores si el plugin no está instalado.
 */
class DataSourceRegistry
{
    /**
     * Devuelve todas las fuentes de datos disponibles.
     *
     * Estructura de cada entrada:
     *   'label'          => string  (nombre legible)
     *   'class'          => string  (FQCN del modelo Eloquent)
     *   'date_fields'    => array   (['campo' => 'Etiqueta'])
     *   'numeric_fields' => array   (['campo' => 'Etiqueta']) — sin incluir '__count__'
     */
    public static function getAvailable(): array
    {
        // --- Modelos del core (siempre disponibles) ---
        $sources = [
            'Factura' => [
                'label'          => 'Facturas emitidas',
                'class'          => \App\Models\Factura::class,
                'date_fields'    => [
                    'fecha'      => 'Fecha de emisión',
                    'vencimiento' => 'Fecha de vencimiento',
                ],
                'numeric_fields' => [
                    'base'       => 'Base imponible',
                    'iva_total'  => 'Total IVA',
                    'irpf_total' => 'Total IRPF',
                    'total'      => 'Total',
                ],
            ],

            'Pago' => [
                'label'          => 'Pagos recibidos',
                'class'          => \App\Models\Pago::class,
                'date_fields'    => [
                    'fecha_pago' => 'Fecha de pago',
                ],
                'numeric_fields' => [
                    'importe' => 'Importe',
                ],
            ],

            'FacturasProveedor' => [
                'label'          => 'Facturas de proveedor',
                'class'          => \App\Models\FacturasProveedor::class,
                'date_fields'    => [
                    'fecha' => 'Fecha',
                ],
                'numeric_fields' => [
                    'base'      => 'Base imponible',
                    'iva_total' => 'IVA',
                    'total'     => 'Total',
                ],
            ],
        ];

        // --- Plugin Gastos ---
        if (class_exists(\Gastos\Models\Gasto::class)) {
            $sources['Gasto'] = [
                'label'          => 'Gastos',
                'class'          => \Gastos\Models\Gasto::class,
                'date_fields'    => [
                    'fecha' => 'Fecha',
                ],
                'numeric_fields' => [
                    'importe' => 'Importe',
                ],
            ];
        }

        // --- Plugin Presupuestos ---
        if (class_exists(\Presupuestos\Models\Presupuesto::class)) {
            $sources['Presupuesto'] = [
                'label'          => 'Presupuestos',
                'class'          => \Presupuestos\Models\Presupuesto::class,
                'date_fields'    => [
                    'fecha'      => 'Fecha de emisión',
                    'vencimiento' => 'Fecha de vencimiento',
                ],
                'numeric_fields' => [
                    'base'      => 'Base imponible',
                    'iva_total' => 'IVA',
                    'total'     => 'Total',
                ],
            ];
        }

        // --- Plugin WooCommerce ---
        if (class_exists(\Woocommerce\Models\WooPedidoImportado::class)) {
            $sources['WooPedido'] = [
                'label'          => 'Pedidos WooCommerce',
                'class'          => \Woocommerce\Models\WooPedidoImportado::class,
                'date_fields'    => [
                    'created_at' => 'Fecha de importación',
                ],
                // Sin campos numéricos propios; usar '__count__' para conteo de pedidos
                'numeric_fields' => [],
            ];
        }

        return $sources;
    }

    /** Sentinel para fuentes de query personalizada. */
    public const CUSTOM_QUERY = '__custom_query__';

    /** Devuelve ['ModeloKey' => 'Label'] para usar en Select de Filament. */
    public static function getModelOptions(bool $includeCustomQuery = false): array
    {
        $options = array_map(fn($s) => $s['label'], static::getAvailable());

        if ($includeCustomQuery) {
            $options[self::CUSTOM_QUERY] = 'Query SQL personalizada (solo admins)';
        }

        return $options;
    }

    public static function isCustomQuery(string $modelo): bool
    {
        return $modelo === self::CUSTOM_QUERY;
    }

    /** Campos de fecha del modelo (para el eje X o filtro de fecha). */
    public static function getDateFields(string $modelo): array
    {
        return static::getAvailable()[$modelo]['date_fields'] ?? [];
    }

    /**
     * Campos numéricos del modelo más '__count__' (siempre disponible).
     * Usado para el eje Y.
     */
    public static function getNumericFields(string $modelo): array
    {
        $source = static::getAvailable()[$modelo] ?? null;
        if (!$source) {
            return ['__count__' => 'Número de registros'];
        }

        return array_merge(
            $source['numeric_fields'],
            ['__count__' => 'Número de registros']
        );
    }

    /** FQCN del modelo Eloquent. */
    public static function getModelClass(string $modelo): ?string
    {
        return static::getAvailable()[$modelo]['class'] ?? null;
    }

    public static function exists(string $modelo): bool
    {
        return isset(static::getAvailable()[$modelo]);
    }
}
