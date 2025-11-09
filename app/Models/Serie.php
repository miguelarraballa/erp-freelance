<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $fillable = ['codigo','prefijo','sufijo','siguiente_numero','por_defecto','activo'];

    public function facturas() { return $this->hasMany(Factura::class); }
}