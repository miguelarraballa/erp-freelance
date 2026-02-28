<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('asunto');
            $table->longText('cuerpo_html');
            $table->longText('cuerpo_texto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_plantillas');
    }
};
