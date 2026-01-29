<?php

namespace Gastos\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'fecha',
        'importe',
    ];

    protected $casts = [
        'fecha' => 'date',
        'importe' => 'decimal:2',
    ];
}
