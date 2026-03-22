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
            $table->unsignedTinyInteger('ancho')->default(50)->after('orden');
        });
    }

    public function down(): void
    {
        Schema::table('graficas', function (Blueprint $table) {
            $table->dropColumn('ancho');
        });
    }
};
