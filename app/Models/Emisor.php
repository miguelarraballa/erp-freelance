<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emisor extends Model
{   

    protected $table = 'emisores';
    
    protected $fillable = [
        'nombre','nombre_comercial','nif','direccion','cp','ciudad','provincia','pais',
        'email','telefono','web','iban','swift_bic','logo_path',
        'pie_factura','notas_legales','activo',
    ];

    protected $casts = [
        'activo' => 'bool',
    ];

    // Siempre uno activo
    protected static function booted(): void
    {
        static::saving(function (Emisor $e) {
            if ($e->isDirty('activo') && $e->activo) {
                static::whereKeyNot($e->id)->update(['activo' => false]);
            }
        });
    }

    public function scopeActivo($q)
    {
        return $q->where('activo', true);
    }
}