<?php

namespace Woocommerce\Models;

use App\Models\Factura;
use Illuminate\Database\Eloquent\Model;

class WooPedidoImportado extends Model
{
    protected $table = 'woo_pedidos_importados';

    protected $fillable = [
        'tienda_id',
        'woo_order_id',
        'factura_id',
        'woo_status',
    ];

    public function tienda()
    {
        return $this->belongsTo(TiendaWoo::class, 'tienda_id');
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }
}
