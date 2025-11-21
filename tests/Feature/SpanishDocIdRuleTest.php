<?php

namespace Tests\Feature; // pon Tests\Unit si lo mueves a /tests/Unit

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Rules\SpanishDocIdRule;

class SpanishDocIdRuleTest extends TestCase
{
    public function test_regla_valida_y_falla_cuando_corresponde(): void
    {
        $ok = Validator::make(
            ['nif' => '12345678Z'],
            ['nif' => [new SpanishDocIdRule()]]
        )->passes();

        $ko = Validator::make(
            ['nif' => '12345678A'],
            ['nif' => [new SpanishDocIdRule()]]
        )->passes();

        $this->assertTrue($ok);
        $this->assertFalse($ko);
    }
}