<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->enum('tipo', ['normal','rectificativa','abono','proveedor','presupuesto'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->enum('tipo', ['normal','rectificativa','abono','proveedor'])->change();
        });
    }
};
