<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos_tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->text('descripcion');
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->time('inicio')->default(DB::raw('CURRENT_TIME'));
            $table->time('fin')->default(DB::raw("ADDTIME(CURRENT_TIME, '01:00:00')"));
            $table->boolean('facturado')->default(false);
            $table->decimal('precio', 8, 2)->default(15.00);
            $table->decimal('duracion', 8, 2)->default(1.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos_tareas');
    }
};
