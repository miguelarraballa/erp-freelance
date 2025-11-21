<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class CreateClienteAssignsCodigoTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigns_codigo_when_cliente_and_empty(): void
    {
        DB::table('clientes_autonumerico')->updateOrInsert(
            ['tipo' => 'cliente'],
            ['siguiente_numero' => 1]
        );

        // Simulación mínima vía modelo (sin Filament/Livewire):
        $data = [
            'nombre'    => 'Acme S.A.',
            'nif'       => 'B12345674',
            'cliente'   => 1,
            'proveedor' => 0,
            'pais'      => 'ES',
        ];

        // Emula la lógica de CreateCliente (si ahí generas el código en mutate...):
        if (!empty($data['cliente']) && empty($data['codigo_cliente'])) {
            $data['codigo_cliente'] = \App\Services\CodigoSecuencialService::next('cliente', 'C', 4);
        }

        $c = Cliente::create($data);

        $this->assertSame('C0001', $c->codigo_cliente);
    }
}