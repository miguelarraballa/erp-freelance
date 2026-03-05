<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiendas_woo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('url');                        // https://mitienda.com
            $table->string('consumer_key');               // ck_xxx
            $table->string('consumer_secret');            // cs_xxx
            $table->foreignId('serie_id')->constrained('series')->restrictOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->timestamp('ultima_sincronizacion')->nullable(); // última vez que se importó
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiendas_woo');
    }
};
