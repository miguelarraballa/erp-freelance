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
            $table->tinyInteger('signo')->default(1)->after('orden'); // +1 suma, -1 resta
        });
    }

    public function down(): void
    {
        Schema::table('grafica_fuentes', function (Blueprint $table) {
            $table->dropColumn('signo');
        });
    }
};
