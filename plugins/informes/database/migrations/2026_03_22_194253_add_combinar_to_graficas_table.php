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
        Schema::table('graficas', function (Blueprint $table) {
            $table->boolean('combinar')->default(false)->after('ancho');
            $table->string('etiqueta_combinada')->nullable()->after('combinar');
        });
    }

    public function down(): void
    {
        Schema::table('graficas', function (Blueprint $table) {
            $table->dropColumn(['combinar', 'etiqueta_combinada']);
        });
    }
};
