<?php

namespace Informes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Informe extends Model
{
    protected $table = 'informes';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function graficas(): HasMany
    {
        return $this->hasMany(Grafica::class)->orderBy('orden');
    }
}
