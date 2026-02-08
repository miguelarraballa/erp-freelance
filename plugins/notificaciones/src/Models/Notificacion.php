<?php

namespace Notificaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Notificaciones\Enums\NotificacionEstado;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'notificacion_plantilla_id',
        'email_destinatario',
        'asunto_procesado',
        'cuerpo_html_procesado',
        'cuerpo_texto_procesado',
        'fecha',
        'estado',
        'error',
        'fecha_envio',
        'relacionado_tabla',
        'relacionado_id',
        'adjuntable_type',
        'adjuntable_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_envio' => 'datetime',
        'estado' => NotificacionEstado::class,
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(NotificacionPlantilla::class, 'notificacion_plantilla_id');
    }

    public function adjuntable(): MorphTo
    {
        return $this->morphTo();
    }
}
