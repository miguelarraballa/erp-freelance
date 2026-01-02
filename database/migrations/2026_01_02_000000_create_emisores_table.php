<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emisores', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');                 // Razón social
            $t->string('nif', 20)->nullable();    // Valida con SpanishDocIdRule
            $t->string('direccion')->nullable();
            $t->string('cp', 10)->nullable();
            $t->string('ciudad')->nullable();
            $t->string('provincia')->nullable();
            $t->string('pais', 2)->nullable();    // ISO-3166-1 alpha-2
            $t->string('email')->nullable();
            $t->string('telefono')->nullable();
            $t->string('web')->nullable();

            // Bancarios
            $t->string('iban', 34)->nullable();
            $t->string('swift_bic', 11)->nullable();

            // Branding / legales
            $t->string('logo_path')->nullable();
            $t->text('pie_factura')->nullable();
            $t->text('notas_legales')->nullable();

            $t->boolean('activo')->default(true);

            // Forzamos que solo haya uno activo mediante columna generada
            $t->string('activo_key', 8)
              ->nullable()
              ->storedAs("CASE WHEN activo = 1 THEN 'emisor' ELSE NULL END");
            $t->unique('activo_key');

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emisores');
    }
};