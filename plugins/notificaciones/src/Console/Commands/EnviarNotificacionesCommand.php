<?php

namespace Notificaciones\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Notificaciones\Models\Notificacion;
use Notificaciones\Enums\NotificacionEstado;
use Notificaciones\Mail\NotificacionEmail;

class EnviarNotificacionesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:enviar
                            {--limit=50 : Número máximo de emails a enviar por ejecución}
                            {--debug : Mostrar información detallada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía los emails en cola del sistema de notificaciones';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $debug = $this->option('debug');

        if ($debug) {
            $this->info('Iniciando envío de notificaciones...');
        }

        // Get pending notifications
        $notificaciones = Notificacion::with(['plantilla', 'adjuntable'])
            ->where('estado', NotificacionEstado::EnCola)
            ->orderBy('fecha', 'asc')
            ->limit($limit)
            ->get();

        if ($notificaciones->isEmpty()) {
            if ($debug) {
                $this->info('No hay notificaciones pendientes de envío.');
            }
            return self::SUCCESS;
        }

        $enviados = 0;
        $errores = 0;

        foreach ($notificaciones as $notificacion) {
            try {
                if ($debug) {
                    $this->line("Enviando a: {$notificacion->email_destinatario}");
                }

                // Send email
                Mail::to($notificacion->email_destinatario)
                    ->send(new NotificacionEmail($notificacion));

                // Update notification status
                $notificacion->update([
                    'estado' => NotificacionEstado::Enviado,
                    'fecha_envio' => now(),
                    'error' => null,
                ]);

                $enviados++;

                if ($debug) {
                    $this->info("✓ Email enviado exitosamente");
                }

            } catch (\Exception $e) {
                $errores++;

                // Log error
                Log::error('Error enviando notificación', [
                    'notificacion_id' => $notificacion->id,
                    'email' => $notificacion->email_destinatario,
                    'error' => $e->getMessage(),
                ]);

                // Update notification with error
                $notificacion->update([
                    'estado' => NotificacionEstado::Error,
                    'fecha_envio' => now(),
                    'error' => substr($e->getMessage(), 0, 500), // Limit error message length
                ]);

                if ($debug) {
                    $this->error("✗ Error: {$e->getMessage()}");
                }
            }
        }

        // Summary
        $this->info("Proceso completado: {$enviados} enviados, {$errores} errores");

        return self::SUCCESS;
    }
}
