<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesAutonumerico extends Model
{
    protected $table = 'clientes_autonumerico';
    public $timestamps = false;

    protected $fillable = ['tipo', 'siguiente_numero'];
}