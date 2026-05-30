<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servidores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nombre', 250);
            $table->string('url', 250);
            $table->string('dominio', 250);
            $table->date('fecha_alta')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_renovacion')->default(DB::raw("(CURRENT_DATE + INTERVAL 1 YEAR)"));
            $table->string('paquete', 250)->nullable();
            $table->decimal('precio', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servidores');
    }
};
