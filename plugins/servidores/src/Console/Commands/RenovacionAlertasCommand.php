<?php

namespace Servidores\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Notificaciones\Helpers\NotificacionesHelper;
use Notificaciones\Models\Notificacion;
use Notificaciones\Models\NotificacionPlantilla;
use Servidores\Models\Servidor;

class RenovacionAlertasCommand extends Command
{
    protected $signature = 'servidores:renovacion-alertas
                            {--debug : Mostrar información detallada}';

    protected $description = 'Encola alertas por email al emisor activo cuando un servidor se acerca a su fecha de renovación (avisos a 1 mes y 15 días).';

    public function handle(): int
    {
        if (! config('plugins.notificaciones', true)) {
            $this->warn('El plugin de notificaciones está desactivado. No se enviarán alertas.');
            return self::SUCCESS;
        }

        if (! class_exists(NotificacionesHelper::class)) {
            $this->error('El plugin de notificaciones no está disponible.');
            return self::FAILURE;
        }

        $debug = $this->option('debug');

        $plantilla1Mes = NotificacionPlantilla::where('nombre', 'renovacion_servidores_1mes')->first();
        $plantilla15Dias = NotificacionPlantilla::where('nombre', 'renovacion_servidores_15dias')->first();

        if (! $plantilla1Mes || ! $plantilla15Dias) {
            $this->error('Plantillas de renovación no encontradas. Ejecuta las migraciones del plugin servidores.');
            return self::FAILURE;
        }

        $emisorActivo = DB::table('emisores')->where('activo', true)->first();

        if (! $emisorActivo || ! $emisorActivo->email) {
            $this->error('No hay emisor activo con email configurado.');
            return self::FAILURE;
        }

        $hoy = Carbon::today();
        $encolados = 0;
        $yaNotificados = 0;
        $errores = 0;

        // Alerta 1 mes: ventana de 28–32 días para cubrir fallos ocasionales del cron
        $servidores1Mes = Servidor::with('cliente')
            ->where('activo', true)
            ->whereBetween('fecha_renovacion', [
                $hoy->copy()->addDays(28),
                $hoy->copy()->addDays(32),
            ])
            ->get();

        if ($debug) {
            $this->info("Servidores en ventana 1 mes: {$servidores1Mes->count()}");
        }

        foreach ($servidores1Mes as $servidor) {
            $resultado = $this->procesarAlerta($servidor, $plantilla1Mes, $emisorActivo, $debug);
            match ($resultado) {
                'encolado' => $encolados++,
                'ya_notificado' => $yaNotificados++,
                default => $errores++,
            };
        }

        // Alerta 15 días: ventana de 13–17 días
        $servidores15Dias = Servidor::with('cliente')
            ->where('activo', true)
            ->whereBetween('fecha_renovacion', [
                $hoy->copy()->addDays(13),
                $hoy->copy()->addDays(17),
            ])
            ->get();

        if ($debug) {
            $this->info("Servidores en ventana 15 días: {$servidores15Dias->count()}");
        }

        foreach ($servidores15Dias as $servidor) {
            $resultado = $this->procesarAlerta($servidor, $plantilla15Dias, $emisorActivo, $debug);
            match ($resultado) {
                'encolado' => $encolados++,
                'ya_notificado' => $yaNotificados++,
                default => $errores++,
            };
        }

        $this->info("Proceso completado: {$encolados} encolados, {$yaNotificados} ya notificados, {$errores} errores");

        return self::SUCCESS;
    }

    private function procesarAlerta(
        Servidor $servidor,
        NotificacionPlantilla $plantilla,
        object $emisorActivo,
        bool $debug,
    ): string {
        // Deduplicación: no reenviar si ya se notificó en los últimos 5 días con esta misma plantilla
        $yaNotificado = Notificacion::where('relacionado_tabla', 'servidores')
            ->where('relacionado_id', $servidor->id)
            ->where('notificacion_plantilla_id', $plantilla->id)
            ->where('fecha', '>=', Carbon::now()->subDays(5))
            ->exists();

        if ($yaNotificado) {
            if ($debug) {
                $this->line("  Saltando servidor #{$servidor->id} ({$servidor->nombre}) — ya notificado recientemente con '{$plantilla->nombre}'");
            }
            return 'ya_notificado';
        }

        try {
            NotificacionesHelper::queueEmail(
                plantillaNombre: $plantilla->nombre,
                emailDestinatario: $emisorActivo->email,
                context: [
                    'servidores' => $servidor->id,
                    'clientes' => $servidor->cliente_id,
                    'emisores' => $emisorActivo->id,
                ],
                relacionadoTabla: 'servidores',
                relacionadoId: $servidor->id,
            );

            if ($debug) {
                $this->info("  Servidor #{$servidor->id} ({$servidor->nombre}) → alerta encolada [{$plantilla->nombre}] → {$emisorActivo->email}");
            }

            return 'encolado';
        } catch (\Exception $e) {
            Log::error('Error encolando alerta de renovación de servidor', [
                'servidor_id' => $servidor->id,
                'plantilla' => $plantilla->nombre,
                'email' => $emisorActivo->email,
                'error' => $e->getMessage(),
            ]);

            if ($debug) {
                $this->error("  Servidor #{$servidor->id} ({$servidor->nombre}) — error: {$e->getMessage()}");
            }

            return 'error';
        }
    }
}
