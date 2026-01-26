<?php

namespace Notificaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificacionPlantilla extends Model
{
    protected $table = 'notificaciones_plantillas';

    protected $fillable = [
        'nombre',
        'asunto',
        'cuerpo_html',
        'cuerpo_texto',
    ];

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'notificacion_plantilla_id');
    }
}
