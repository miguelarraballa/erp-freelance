<?php

namespace Notificaciones\Console\Commands;

use App\Models\Factura;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Notificaciones\Helpers\NotificacionesHelper;
use Notificaciones\Models\Notificacion;
use Notificaciones\Models\NotificacionPlantilla;

class RecordarFacturasVencidasCommand extends Command
{
    protected $signature = 'notificaciones:recordar-vencidas
                            {--debug : Mostrar información detallada}';

    protected $description = 'Encola recordatorios por email para facturas vencidas (emitidas con vencimiento pasado). Se ejecuta cada 7 días por factura.';

    public function handle(): int
    {
        $debug = $this->option('debug');
        $plantillaNombre = 'factura_pendiente';
        $diasEntreRecordatorios = 7;

        if ($debug) {
            $this->info('Buscando facturas vencidas...');
        }

        // Verificar que la plantilla existe
        $plantilla = NotificacionPlantilla::where('nombre', $plantillaNombre)->first();

        if (! $plantilla) {
            $this->error("Plantilla '{$plantillaNombre}' no encontrada. Créala desde el panel de administración.");
            return self::FAILURE;
        }

        // Buscar facturas emitidas con vencimiento pasado
        $facturas = Factura::with('cliente')
            ->where('estado', 'emitida')
            ->whereNotNull('vencimiento')
            ->where('vencimiento', '<', Carbon::today())
            ->get();

        if ($facturas->isEmpty()) {
            if ($debug) {
                $this->info('No hay facturas vencidas.');
            }
            return self::SUCCESS;
        }

        if ($debug) {
            $this->info("Encontradas {$facturas->count()} facturas vencidas.");
        }

        $encolados = 0;
        $sinEmail = 0;
        $yaNotificados = 0;
        $errores = 0;
        $emisorActivo = DB::table('emisores')->where('activo', true)->value('id');

        foreach ($facturas as $factura) {
            // Comprobar si ya se envió recordatorio en los últimos X días
            $recordatorioReciente = Notificacion::where('relacionado_tabla', 'facturas')
                ->where('relacionado_id', $factura->id)
                ->where('notificacion_plantilla_id', $plantilla->id)
                ->where('fecha', '>=', Carbon::now()->subDays($diasEntreRecordatorios))
                ->exists();

            if ($recordatorioReciente) {
                $yaNotificados++;
                if ($debug) {
                    $this->line("  Saltando factura #{$factura->id} - ya notificada recientemente");
                }
                continue;
            }

            // Obtener email del cliente
            $email = $factura->cliente->email_facturacion
                ?? $factura->cliente->contacto_email
                ?? null;

            if (! $email) {
                $sinEmail++;
                if ($debug) {
                    $this->warn("  Factura #{$factura->id} - cliente sin email");
                }
                Log::warning('Recordatorio factura vencida: cliente sin email', [
                    'factura_id' => $factura->id,
                    'cliente_id' => $factura->cliente_id,
                ]);
                continue;
            }

            try {
                NotificacionesHelper::queueEmail(
                    plantillaNombre: $plantillaNombre,
                    emailDestinatario: $email,
                    context: [
                        'facturas' => $factura->id,
                        'clientes' => $factura->cliente_id,
                        'emisores' => $emisorActivo,
                    ],
                    relacionadoTabla: 'facturas',
                    relacionadoId: $factura->id,
                    adjuntable: $factura,
                );

                $encolados++;

                if ($debug) {
                    $this->info("  Factura #{$factura->id} -> recordatorio encolado a {$email}");
                }
            } catch (\Exception $e) {
                $errores++;
                Log::error('Error encolando recordatorio de factura vencida', [
                    'factura_id' => $factura->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);

                if ($debug) {
                    $this->error("  Factura #{$factura->id} - error: {$e->getMessage()}");
                }
            }
        }

        $this->info("Proceso completado: {$encolados} encolados, {$yaNotificados} ya notificados, {$sinEmail} sin email, {$errores} errores");

        return self::SUCCESS;
    }
}
