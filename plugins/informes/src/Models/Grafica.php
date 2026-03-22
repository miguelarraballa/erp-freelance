<?php

namespace Informes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grafica extends Model
{
    protected $table = 'graficas';

    protected $fillable = [
        'informe_id',
        'nombre',
        'tipo',
        'fecha_desde',
        'fecha_hasta',
        'granularidad',
        'orden',
        'ancho',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'orden'       => 'integer',
        'ancho'       => 'integer',
    ];

    // Tipos que no tienen eje X/Y (solo un valor agregado por fuente)
    public const TIPO_STAT = 'stat';

    // Tipos que no usan granularidad temporal
    public const TIPOS_SIN_GRANULARIDAD = ['stat', 'pie', 'donut'];

    public function informe(): BelongsTo
    {
        return $this->belongsTo(Informe::class);
    }

    public function fuentes(): HasMany
    {
        return $this->hasMany(GraficaFuente::class)->orderBy('orden');
    }

    public function isStat(): bool
    {
        return $this->tipo === self::TIPO_STAT;
    }

    public function needsGranularidad(): bool
    {
        return !in_array($this->tipo, self::TIPOS_SIN_GRANULARIDAD);
    }
}
