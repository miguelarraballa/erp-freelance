<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaLog extends Model
{
    public $timestamps = false; // usamos created_at manual
    protected $fillable = ['factura_id','user_id','evento','datos','ip','user_agent','prev_hash','hash','created_at'];

    protected $casts = ['datos' => 'array', 'created_at' => 'datetime'];

    public function factura() { return $this->belongsTo(Factura::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
