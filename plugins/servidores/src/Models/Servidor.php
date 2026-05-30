<?php

namespace Servidores\Models;

use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servidor extends Model
{
    protected $table = 'servidores';

    protected $fillable = [
        'cliente_id',
        'nombre',
        'url',
        'dominio',
        'fecha_alta',
        'fecha_renovacion',
        'paquete',
        'precio',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_alta'      => 'date',
        'fecha_renovacion' => 'date',
        'precio'          => 'decimal:2',
        'activo'          => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function facturas(): BelongsToMany
    {
        return $this->belongsToMany(Factura::class, 'servidor_facturas')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }
}
