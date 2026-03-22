<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grafica_fuentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grafica_id')->constrained('graficas')->cascadeOnDelete();
            // Clave del modelo en DataSourceRegistry (e.g. 'Factura', 'Gasto')
            $table->string('modelo');
            $table->string('nombre_display');
            $table->string('color', 20)->nullable();
            // Campo de fecha: eje X en charts de series, filtro de fecha en stat/pie/donut
            $table->string('campo_x')->nullable();
            // Campo numérico o '__count__' para conteo
            $table->string('campo_y');
            $table->enum('agregacion_y', ['sum', 'count', 'avg', 'median'])->default('sum');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grafica_fuentes');
    }
};
