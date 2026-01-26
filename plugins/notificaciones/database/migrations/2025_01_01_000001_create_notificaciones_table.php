<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notificacion_plantilla_id')
                ->constrained('notificaciones_plantillas');
            $table->json('datos')->nullable();
            $table->dateTime('fecha');
            $table->enum('estado', ['en_cola', 'enviado', 'error'])->default('en_cola');
            $table->text('error')->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->string('relacionado_tabla');
            $table->unsignedBigInteger('relacionado_id');
            $table->timestamps();

            $table->index(['estado', 'fecha']);
            $table->index(['relacionado_tabla', 'relacionado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
