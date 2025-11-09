<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = [
        'serie_id','numero','numero_completo','cliente_id','fecha','vencimiento','estado','tipo',
        'verificacion_hash','base','iva_total','irpf_total','total','moneda','notas',
        'rectifica_id','created_by','updated_by',
    ];

    public function serie()   { 
        return $this->belongsTo(Serie::class); 
    }

    public function cliente() { 
        return $this->belongsTo(Cliente::class); 
    }
    
    public function lineas()  { 
        return $this->hasMany(FacturaLinea::class)->orderBy('orden'); 
    }

    public function pagos()  { 
        return $this->hasMany(Pago::class)->orderBy('fecha'); 
    }
    
    public function logs()    { 
        return $this->hasMany(FacturaLog::class)->latest('id'); 
    }

    public function rectifica()
    {
        return $this->belongsTo(self::class, 'rectifica_id');
    }

    public function rectificadas()
    {
        return $this->hasMany(self::class, 'rectifica_id');
    }

}
