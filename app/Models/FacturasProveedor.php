<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedor extends Model
{
    protected $table = 'facturas_proveedor';

    protected $fillable = [
        'cliente_id','serie_id','numero_proveedor','fecha','concepto',
        'base','iva_total','irpf_total','total','moneda','numero','numero_completo',
        'pdf_path',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function serie() { return $this->belongsTo(Serie::class); }
    public function impuestos() { return $this->hasMany(FacturasProveedorImpuesto::class); }
}
