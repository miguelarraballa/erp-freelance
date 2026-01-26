<?php

namespace Proyectos\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoTarea extends Model
{
    protected $table = 'proyectos_tareas';

    protected $fillable = [
        'proyecto_id',
        'descripcion',
        'fecha',
        'inicio',
        'fin',
        'facturado',
        'precio',
        'duracion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'facturado' => 'boolean',
        'precio' => 'decimal:2',
        'duracion' => 'decimal:2',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
}
