<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graficas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informe_id')->constrained('informes')->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('tipo', [
                'line',
                'bar',
                'bar_horizontal',
                'area',
                'pie',
                'donut',
                'scatter',
                'stat',
            ])->default('line');
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            // Granularidad temporal del eje X (no aplica para stat/pie/donut)
            $table->enum('granularidad', ['dia', 'semana', 'mes', 'anio'])->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graficas');
    }
};
