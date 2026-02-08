<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones_plantillas', function (Blueprint $table) {
            $table->text('html_personalizado')->nullable()->after('cuerpo_html');
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones_plantillas', function (Blueprint $table) {
            $table->dropColumn('html_personalizado');
        });
    }
};
