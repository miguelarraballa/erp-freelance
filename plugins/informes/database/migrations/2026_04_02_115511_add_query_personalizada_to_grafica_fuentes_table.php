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
        Schema::table('grafica_fuentes', function (Blueprint $table) {
            // SQL SELECT personalizado; solo se usa cuando modelo = '__custom_query__'
            $table->text('query_personalizada')->nullable()->after('signo');
        });
    }

    public function down(): void
    {
        Schema::table('grafica_fuentes', function (Blueprint $table) {
            $table->dropColumn('query_personalizada');
        });
    }
};
