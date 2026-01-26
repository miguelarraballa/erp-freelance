<?php

namespace Proyectos\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    protected $fillable = [
        'cliente_id',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'cerrado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'cerrado' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tareas()
    {
        return $this->hasMany(ProyectoTarea::class, 'proyecto_id');
    }
}
