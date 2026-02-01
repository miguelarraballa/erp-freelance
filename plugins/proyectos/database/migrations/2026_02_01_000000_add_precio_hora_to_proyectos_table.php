<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->decimal('precio_hora', 8, 2)->default(15.00)->after('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn('precio_hora');
        });
    }
};
