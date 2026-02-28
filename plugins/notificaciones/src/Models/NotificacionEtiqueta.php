<?php

namespace Notificaciones\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionEtiqueta extends Model
{
    protected $table = 'notificaciones_etiquetas';

    protected $fillable = [
        'tag_name',
        'tag_value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
