<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Models\Cliente;

class EmailMatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ensure migrations run
        Artisan::call('migrate');
        // set env key for tests
        putenv('N8N_API_KEY=testkey');
    }

    public function test_returns_client_by_email()
    {
        $cliente = Cliente::create([
            'nombre' => 'Pepe S.L.',
            'razon_social' => 'Pepe Servicios SL',
            'contacto_nombre' => 'Pepe',
            'contacto_email' => 'pepe@example.com',
            'codigo_cliente' => 'C-001',
            'activo' => 1,
        ]);

        $resp = $this->postJson('/api/n8n/email-check', [
            'email' => 'pepe@example.com',
            'subject' => 'Factura 123'
        ], ['X-N8N-API-KEY' => 'testkey']);

        $resp->assertStatus(200);
        $resp->assertJson(['matched' => true, 'match_type' => 'email']);
        $this->assertArrayHasKey('client', $resp->json());
    }

    public function test_similarity_match_on_subject()
    {
        $cliente = Cliente::create([
            'nombre' => 'ACME',
            'razon_social' => 'ACME SL',
            'contacto_nombre' => 'Ana',
            'contacto_email' => 'ana@acme.test',
            'codigo_cliente' => 'C-002',
            'activo' => 1,
        ]);

        $resp = $this->postJson('/api/n8n/email-check', [
            'subject' => 'Pago a ACME por servicio'
        ], ['X-N8N-API-KEY' => 'testkey']);

        $resp->assertStatus(200);
        $this->assertTrue($resp->json('matched'));
        $this->assertEquals('similarity', $resp->json('match_type'));
        $this->assertArrayHasKey('client', $resp->json());
    }

    public function test_rate_limit_applies()
    {
        // throttle is 10 requests per minute
        for ($i = 0; $i < 10; $i++) {
            $resp = $this->postJson('/api/n8n/email-check', ['subject' => 'test'], ['X-N8N-API-KEY' => 'testkey']);
            $resp->assertStatus(200);
        }

        // 11th should be 429
        $resp = $this->postJson('/api/n8n/email-check', ['subject' => 'test'], ['X-N8N-API-KEY' => 'testkey']);
        $resp->assertStatus(429);
    }
}
