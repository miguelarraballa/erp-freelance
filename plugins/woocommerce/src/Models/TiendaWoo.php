<?php

namespace Woocommerce\Models;

use App\Models\Cliente;
use App\Models\Serie;
use Illuminate\Database\Eloquent\Model;

class TiendaWoo extends Model
{
    protected $table = 'tiendas_woo';

    protected $fillable = [
        'nombre',
        'url',
        'consumer_key',
        'consumer_secret',
        'serie_id',
        'cliente_id',
        'ultima_sincronizacion',
        'activo',
    ];

    protected $casts = [
        'ultima_sincronizacion' => 'datetime',
        'activo'                => 'boolean',
    ];

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pedidosImportados()
    {
        return $this->hasMany(WooPedidoImportado::class, 'tienda_id');
    }
}
