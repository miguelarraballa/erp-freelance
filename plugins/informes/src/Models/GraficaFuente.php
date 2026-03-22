<?php

namespace Informes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraficaFuente extends Model
{
    protected $table = 'grafica_fuentes';

    protected $fillable = [
        'grafica_id',
        'modelo',
        'nombre_display',
        'color',
        'campo_x',
        'campo_y',
        'agregacion_y',
        'orden',
        'signo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'signo' => 'integer',
    ];

    public function grafica(): BelongsTo
    {
        return $this->belongsTo(Grafica::class);
    }
}
