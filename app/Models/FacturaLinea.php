<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaLinea extends Model
{
    protected $table = 'factura_lineas';

    protected $fillable = [
        'factura_id','orden','concepto','cantidad','precio_unitario','descuento_pct',
        'impuesto_id','base_linea','iva_linea','irpf_linea','total_linea','producto'
    ];

    public function factura()  { return $this->belongsTo(Factura::class); }
    public function impuesto() { return $this->belongsTo(Impuesto::class); }
}