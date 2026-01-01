<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');     
            $table->string('prefijo')->nullable();  
            $table->string('sufijo')->nullable();   
            $table->unsignedBigInteger('siguiente_numero')->default(1);
            $table->boolean('por_defecto')->default(false);
            $table->boolean('activo')->default(true);
            $table->enum('tipo', ['normal','rectificativa','abono','proveedor'])->default('normal');
            $table->unsignedSmallInteger('ejercicio')->default((int) date('Y'));
            $table->timestamps();

             // clave solo cuando está activa; indexable → storedAs
            $table->string('activo_key', 32)
              ->nullable()
              ->storedAs("CASE WHEN activo = 1 THEN CONCAT(tipo,'-',ejercicio) ELSE NULL END");

            $table->unique('activo_key', 'series_activa_tipo_ejercicio_unique');

            $table->unique(
                ['sufijo', 'codigo', 'prefijo'],
                'series_sufijo_codigo_prefijo_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
