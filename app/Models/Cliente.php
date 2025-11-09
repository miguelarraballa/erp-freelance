<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'razon_social',
        'nif',
        'direccion',
        'datos_facturacion',
        'cp',
        'ciudad',
        'provincia',
        'pais',
        'contacto_nombre',
        'contacto_email',
        'contacto_telefono',
        'cliente',
        'proveedor',
        'irpf',
        'iban',
        'email_facturacion',
        'telefono_facturacion',
        'codigo_cliente',
        'activo',
        'observaciones',
        'fecha_alta',
        'fecha_baja',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
}
