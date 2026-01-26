<?php

namespace Notificaciones\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Notificaciones\Enums\NotificacionEstado;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'notificacion_plantilla_id',
        'datos',
        'fecha',
        'estado',
        'error',
        'fecha_envio',
        'relacionado_tabla',
        'relacionado_id',
    ];

    protected $casts = [
        'datos' => 'array',
        'fecha' => 'datetime',
        'fecha_envio' => 'datetime',
        'estado' => NotificacionEstado::class,
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(NotificacionPlantilla::class, 'notificacion_plantilla_id');
    }

    public static function queue(string $plantilla, array $datos): self
    {
        $required = ['estado', 'relacionado_tabla', 'relacionado_id', 'datos'];
        $missing = [];

        foreach ($required as $key) {
            if (!array_key_exists($key, $datos) || $datos[$key] === null || $datos[$key] === '') {
                $missing[] = $key;
            }
        }

        if ($missing) {
            throw new InvalidArgumentException('Faltan campos requeridos: ' . implode(', ', $missing));
        }

        if (!is_array($datos['datos'])) {
            throw new InvalidArgumentException('El campo datos debe ser un array.');
        }

        $estado = $datos['estado'];
        if ($estado instanceof NotificacionEstado) {
            $estado = $estado->value;
        }

        if (!in_array($estado, array_column(NotificacionEstado::cases(), 'value'), true)) {
            throw new InvalidArgumentException('El campo estado no es valido.');
        }

        $plantillaModel = NotificacionPlantilla::query()
            ->where('nombre', $plantilla)
            ->first();

        if (!$plantillaModel) {
            throw new InvalidArgumentException("Plantilla no encontrada: {$plantilla}");
        }

        return static::create([
            'notificacion_plantilla_id' => $plantillaModel->id,
            'datos' => $datos['datos'],
            'fecha' => $datos['fecha'] ?? now(),
            'estado' => $estado,
            'error' => $datos['error'] ?? null,
            'fecha_envio' => $datos['fecha_envio'] ?? null,
            'relacionado_tabla' => $datos['relacionado_tabla'],
            'relacionado_id' => $datos['relacionado_id'],
        ]);
    }
}
