<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'factura_id',
        'fecha_pago',
        'importe'
    ];

    public function factura()  { 
        return $this->belongsTo(Factura::class); 
    }
    
}
