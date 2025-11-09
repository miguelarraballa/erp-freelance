<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedorImpuesto extends Model
{
    protected $table = 'facturas_proveedor_impuestos';

    protected $fillable = [
        'factura_proveedor_id','porcentaje','base','cuota',
    ];

    public function factura() { return $this->belongsTo(FacturaProveedor::class, 'factura_proveedor_id'); }
}
