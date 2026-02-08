<?php

/**
 * EJEMPLOS DE USO DEL SISTEMA DE NOTIFICACIONES
 *
 * Este archivo contiene ejemplos prácticos de cómo usar el sistema de notificaciones
 * en diferentes escenarios comunes de la aplicación.
 */

use Notificaciones\Helpers\NotificacionesHelper;
use App\Models\Factura;
use App\Models\Presupuesto;
use App\Models\Cliente;

// ============================================================================
// EJEMPLO 1: Enviar factura a cliente
// ============================================================================

/**
 * Enviar una factura generada a un cliente con el PDF adjunto
 */
function enviarFacturaACliente(int $facturaId): void
{
    $factura = Factura::with(['cliente', 'emisor'])->findOrFail($facturaId);

    try {
        $notificacion = NotificacionesHelper::queueEmail(
            plantillaNombre: 'factura_enviada',
            emailDestinatario: $factura->cliente->email,
            context: [
                'facturas' => $factura->id,
                'clientes' => $factura->cliente_id,
                'emisores' => $factura->emisor_id,
            ],
            relacionadoTabla: 'facturas',
            relacionadoId: $factura->id,
            adjuntable: $factura  // El PDF de la factura se adjuntará automáticamente
        );

        echo "Email encolado exitosamente. ID: {$notificacion->id}\n";
    } catch (\InvalidArgumentException $e) {
        echo "Error: {$e->getMessage()}\n";
    }
}

// ============================================================================
// EJEMPLO 2: Enviar presupuesto con información del proyecto
// ============================================================================

/**
 * Enviar un presupuesto a un cliente incluyendo datos del proyecto
 */
function enviarPresupuesto(int $presupuestoId): void
{
    $presupuesto = Presupuesto::with(['cliente', 'proyecto'])->findOrFail($presupuestoId);

    $notificacion = NotificacionesHelper::queueEmail(
        plantillaNombre: 'presupuesto_nuevo',
        emailDestinatario: $presupuesto->cliente->email,
        context: [
            'presupuestos' => $presupuesto->id,
            'clientes' => $presupuesto->cliente_id,
            'proyectos' => $presupuesto->proyecto_id ?? null,
        ],
        adjuntable: $presupuesto
    );

    // El relacionado se auto-detecta del primer elemento del context
    // En este caso será: relacionado_tabla = 'presupuestos', relacionado_id = $presupuesto->id
}

// ============================================================================
// EJEMPLO 3: Recordatorio de pago sin adjuntos
// ============================================================================

/**
 * Enviar un recordatorio de pago a un cliente (sin PDF adjunto)
 */
function enviarRecordatorioPago(int $facturaId): void
{
    $factura = Factura::with('cliente')->findOrFail($facturaId);

    NotificacionesHelper::queueEmail(
        plantillaNombre: 'recordatorio_pago',
        emailDestinatario: $factura->cliente->email,
        context: [
            'facturas' => $factura->id,
            'clientes' => $factura->cliente_id,
        ],
        relacionadoTabla: 'facturas',
        relacionadoId: $factura->id
        // No se especifica adjuntable, por lo que no se adjuntará nada
    );
}

// ============================================================================
// EJEMPLO 4: Notificación genérica a un cliente
// ============================================================================

/**
 * Enviar una notificación genérica usando solo datos del cliente
 */
function enviarBienvenida(int $clienteId): void
{
    $cliente = Cliente::findOrFail($clienteId);

    NotificacionesHelper::queueEmail(
        plantillaNombre: 'bienvenida_cliente',
        emailDestinatario: $cliente->email,
        context: [
            'clientes' => $cliente->id,
        ]
    );
}

// ============================================================================
// EJEMPLO 5: Envío masivo de facturas mensuales
// ============================================================================

/**
 * Enviar todas las facturas de un mes específico a sus respectivos clientes
 */
function enviarFacturasMensuales(int $mes, int $anio): void
{
    $facturas = Factura::whereYear('fecha', $anio)
        ->whereMonth('fecha', $mes)
        ->with(['cliente', 'emisor'])
        ->get();

    $encoladas = 0;
    $errores = 0;

    foreach ($facturas as $factura) {
        try {
            NotificacionesHelper::queueEmail(
                plantillaNombre: 'factura_mensual',
                emailDestinatario: $factura->cliente->email,
                context: [
                    'facturas' => $factura->id,
                    'clientes' => $factura->cliente_id,
                    'emisores' => $factura->emisor_id,
                ],
                adjuntable: $factura
            );
            $encoladas++;
        } catch (\Exception $e) {
            echo "Error en factura {$factura->id}: {$e->getMessage()}\n";
            $errores++;
        }
    }

    echo "Proceso completado: {$encoladas} encoladas, {$errores} errores\n";
}

// ============================================================================
// EJEMPLO 6: Procesar etiquetas en texto personalizado
// ============================================================================

/**
 * Reemplazar etiquetas en un texto personalizado sin crear notificación
 */
function generarMensajePersonalizado(int $facturaId): string
{
    $mensaje = "Estimado/a {{clientes_contacto_nombre}}, su factura {{facturas_numero}} "
             . "por un importe de {{facturas_total}}€ ha sido generada. "
             . "Empresa: {{emisores_nombre}}";

    $factura = Factura::findOrFail($facturaId);

    $mensajeFinal = NotificacionesHelper::replaceTags($mensaje, [
        'facturas' => $factura->id,
        'clientes' => $factura->cliente_id,
        'emisores' => $factura->emisor_id,
    ]);

    return $mensajeFinal;
}

// ============================================================================
// EJEMPLO 7: Obtener valor de una etiqueta específica
// ============================================================================

/**
 * Obtener el valor de una etiqueta específica
 */
function obtenerNombreCliente(int $clienteId): ?string
{
    return NotificacionesHelper::getTagValue(
        tagName: 'clientes_contacto_nombre',
        context: ['clientes' => $clienteId]
    );
}

// ============================================================================
// EJEMPLO 8: Hook en el evento de creación de factura (Filament)
// ============================================================================

/**
 * En tu Resource de Facturas, puedes añadir esto en el método afterCreate
 */
class FacturaResource extends Resource
{
    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateFactura::route('/create')
                ->afterCreate(function ($record) {
                    // Enviar automáticamente la factura al cliente
                    NotificacionesHelper::queueEmail(
                        plantillaNombre: 'factura_enviada',
                        emailDestinatario: $record->cliente->email,
                        context: [
                            'facturas' => $record->id,
                            'clientes' => $record->cliente_id,
                            'emisores' => $record->emisor_id,
                        ],
                        adjuntable: $record
                    );
                }),
        ];
    }
}

// ============================================================================
// EJEMPLO 9: Manejo de errores y validación
// ============================================================================

/**
 * Ejemplo completo con manejo de errores
 */
function enviarConValidacion(int $facturaId): array
{
    try {
        $factura = Factura::with('cliente')->findOrFail($facturaId);

        // Validar que el cliente tenga email
        if (empty($factura->cliente->email)) {
            return [
                'success' => false,
                'error' => 'El cliente no tiene email configurado'
            ];
        }

        // Validar que el email sea válido
        if (!filter_var($factura->cliente->email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => 'El email del cliente no es válido'
            ];
        }

        $notificacion = NotificacionesHelper::queueEmail(
            plantillaNombre: 'factura_enviada',
            emailDestinatario: $factura->cliente->email,
            context: [
                'facturas' => $factura->id,
                'clientes' => $factura->cliente_id,
            ],
            adjuntable: $factura
        );

        return [
            'success' => true,
            'notificacion_id' => $notificacion->id,
            'message' => 'Email encolado para envío'
        ];

    } catch (\InvalidArgumentException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => 'Error inesperado: ' . $e->getMessage()
        ];
    }
}

// ============================================================================
// EJEMPLO 10: Consultar estado de notificaciones
// ============================================================================

use Notificaciones\Models\Notificacion;
use Notificaciones\Enums\NotificacionEstado;

/**
 * Obtener notificaciones por estado
 */
function consultarEstadoNotificaciones(): void
{
    // Notificaciones pendientes
    $pendientes = Notificacion::where('estado', NotificacionEstado::EnCola)->count();
    echo "Pendientes: {$pendientes}\n";

    // Notificaciones enviadas hoy
    $enviadasHoy = Notificacion::where('estado', NotificacionEstado::Enviado)
        ->whereDate('fecha_envio', today())
        ->count();
    echo "Enviadas hoy: {$enviadasHoy}\n";

    // Notificaciones con error
    $errores = Notificacion::where('estado', NotificacionEstado::Error)
        ->with('plantilla')
        ->get();

    echo "Errores: {$errores->count()}\n";
    foreach ($errores as $notif) {
        echo "  - Email: {$notif->email_destinatario}, Error: {$notif->error}\n";
    }

    // Notificaciones de una factura específica
    $notifFactura = Notificacion::where('relacionado_tabla', 'facturas')
        ->where('relacionado_id', 123)
        ->orderBy('fecha', 'desc')
        ->get();

    echo "Notificaciones de factura 123: {$notifFactura->count()}\n";
}
