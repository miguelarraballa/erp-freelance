<?php

namespace Presupuestos\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Impuesto;

class PresupuestoLinea extends Model
{
    protected $table = 'presupuesto_lineas';

    protected $fillable = [
        'presupuesto_id','orden','concepto','cantidad','precio_unitario','descuento_pct',
        'impuesto_id','base_linea','iva_linea','irpf_linea','total_linea','producto'
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class);
    }

    public function impuesto()
    {
        return $this->belongsTo(Impuesto::class);
    }
}
