<?php

namespace App\Services;

use App\Models\ClientesAutonumerico;
use Illuminate\Support\Facades\DB;

class CodigoSecuencialService
{
    /**
     * Devuelve el siguiente código formateado y avanza el contador.
     * $tipo: 'cliente' (luego podrás usar 'proveedor', etc.)
     * $prefijo: 'C' para clientes.
     * $width: 4 -> C0001, C0002, ...
     */
    public static function next(string $tipo, string $prefijo = 'C', int $width = 4): string
    {
        return DB::transaction(function () use ($tipo, $prefijo, $width) {
            // Bloqueo de fila para concurrencia segura
            $row = ClientesAutonumerico::where('tipo', $tipo)->lockForUpdate()->first();

            if (!$row) {
                // Si no existe, la creamos arrancando en 1
                $row = ClientesAutonumerico::create([
                    'tipo' => $tipo,
                    'siguiente_numero' => 1,
                ]);
            }

            $num = (int) $row->siguiente_numero;

            // Prepara el código (C + número con ceros)
            $codigo = $prefijo . str_pad((string) $num, $width, '0', STR_PAD_LEFT);

            // Avanza el contador
            $row->siguiente_numero = $num + 1;
            $row->save();

            return $codigo;
        });
    }
}