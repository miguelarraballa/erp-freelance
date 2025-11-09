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
            $table->timestamps();

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
