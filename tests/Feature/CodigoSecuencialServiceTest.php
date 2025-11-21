<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Services\CodigoSecuencialService;

class CodigoSecuencialServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_and_advances_counter(): void
    {
        DB::table('clientes_autonumerico')->updateOrInsert(
            ['tipo' => 'cliente'],
            ['siguiente_numero' => 73]
        );

        $c1 = CodigoSecuencialService::next('cliente', 'C', 4);
        $c2 = CodigoSecuencialService::next('cliente', 'C', 4);

        $this->assertSame('C0073', $c1);
        $this->assertSame('C0074', $c2);

        $this->assertSame(
            75,
            (int) DB::table('clientes_autonumerico')->where('tipo', 'cliente')->value('siguiente_numero')
        );
    }
}