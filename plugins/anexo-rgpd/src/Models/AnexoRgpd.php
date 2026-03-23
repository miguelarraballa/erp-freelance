<?php

namespace AnexoRgpd\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;

class AnexoRgpd extends Model
{
    protected $table = 'anexos_rgpd';

    protected $fillable = [
        'cliente_id',
        'cliente_nombre',
        'cliente_nif',
        'cliente_direccion',
        'cliente_email',
        'cliente_firmante',
        'cliente_cargo',
        'descripcion_servicio',
        'fecha_inicio',
        'duracion_acceso',
        'accesos',
        'accesos_otros',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'accesos'      => 'array',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public static function accesoLabels(): array
    {
        return [
            'wordpress'   => 'WordPress',
            'hosting'     => 'Hosting / cPanel',
            'base_datos'  => 'Base de datos',
            'woocommerce' => 'WooCommerce',
            'formularios' => 'Formularios',
            'correo'      => 'Correo',
            'tpv'         => 'TPV / RedSys',
            'analytics'   => 'Analytics / Search Console / Tag Manager',
            'otros'       => 'Otros',
        ];
    }
}
