<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Serie;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SerieActivationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_one_active_per_tipo_and_ejercicio_after_activation(): void
    {
        $year = (int) now()->year;

        $s1 = Serie::create([
            'codigo' => 'N-1',
            'tipo' => 'normal',
            'ejercicio' => $year,
            'siguiente_numero' => 1,
            'activo' => true,
        ]);

        // Otra del mismo tipo+ejercicio, inicialmente inactiva
        $s2 = Serie::create([
            'codigo' => 'N-2',
            'tipo' => 'normal',
            'ejercicio' => $year,
            'siguiente_numero' => 1,
            'activo' => false,
        ]);

        // Distinto tipo (no debe verse afectada)
        $abono = Serie::create([
            'codigo' => 'A-1',
            'tipo' => 'abono',
            'ejercicio' => $year,
            'siguiente_numero' => 1,
            'activo' => true,
        ]);

        // Activamos la s2 → el hook debe desactivar s1
        $s2->activo = true;
        $s2->save();

        $this->assertFalse((bool) $s1->fresh()->activo, 'La serie previamente activa del mismo tipo+ejercicio debería desactivarse.');
        $this->assertTrue((bool) $s2->fresh()->activo,  'La nueva serie debería estar activa.');
        $this->assertTrue((bool) $abono->fresh()->activo, 'Series de otro tipo no deben verse afectadas.');
    }

    /** @test */
    public function unique_generated_column_blocks_two_actives_same_tipo_and_ejercicio(): void
    {
        $year = (int) now()->year;

        Serie::create([
            'codigo' => 'N-1',
            'tipo' => 'normal',
            'ejercicio' => $year,
            'siguiente_numero' => 1,
            'activo' => true,
        ]);

        // Intento bruto de crear otra activa igual (saltándonos el hook del modelo con inserción directa)
        $this->expectException(QueryException::class);

        DB::table('series')->insert([
            'codigo' => 'N-2',
            'tipo' => 'normal',
            'ejercicio' => $year,
            'siguiente_numero' => 1,
            'activo' => 1,
            // si tu tabla requiere timestamps:
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function activo_key_is_generated_only_when_active(): void
    {
        $year = (int) now()->year;

        $s = Serie::create([
            'codigo' => 'R-1',
            'tipo' => 'rectificativa',
            'ejercicio' => $year,
            'siguiente_numero' => 1,
            'activo' => true,
        ]);

        // Columna generada debe ser tipo-ejercicio
        $this->assertSame("rectificativa-{$year}", $s->fresh()->activo_key);

        // Al desactivar, la columna generada debe pasar a NULL
        $s->activo = false;
        $s->save();

        $this->assertNull($s->fresh()->activo_key);
    }
}