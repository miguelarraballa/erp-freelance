<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_etiquetas', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name')->unique();
            $table->string('tag_value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_etiquetas');
    }
};
