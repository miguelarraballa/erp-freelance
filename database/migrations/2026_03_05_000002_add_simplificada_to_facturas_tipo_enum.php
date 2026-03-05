<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE facturas MODIFY COLUMN tipo ENUM('normal','rectificativa','abono','simplificada') NOT NULL DEFAULT 'normal'");
    }

    public function down(): void
    {
        DB::statement("UPDATE facturas SET tipo = 'normal' WHERE tipo = 'simplificada'");
        DB::statement("ALTER TABLE facturas MODIFY COLUMN tipo ENUM('normal','rectificativa','abono') NOT NULL DEFAULT 'normal'");
    }
};
