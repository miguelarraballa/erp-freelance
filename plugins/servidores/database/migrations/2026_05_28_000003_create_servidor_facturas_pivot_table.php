<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla pivot propia del plugin, sin tocar el core
        Schema::create('servidor_facturas', function (Blueprint $table) {
            $table->foreignId('servidor_id')->constrained('servidores')->cascadeOnDelete();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->primary(['servidor_id', 'factura_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servidor_facturas');

        Schema::table('facturas', function (Blueprint $table) {
            $table->foreignId('servidor_id')
                ->nullable()
                ->after('cliente_id')
                ->constrained('servidores')
                ->nullOnDelete();
        });
    }
};
