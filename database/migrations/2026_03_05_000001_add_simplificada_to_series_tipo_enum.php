<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La BD ya tiene 'presupuesto' en el enum (añadido antes de esta migración)
        DB::statement("ALTER TABLE series MODIFY COLUMN tipo ENUM('normal','rectificativa','abono','proveedor','presupuesto','simplificada') NOT NULL DEFAULT 'normal'");
    }

    public function down(): void
    {
        // Antes de revertir, convertir simplificada a normal para no violar la constraint
        DB::statement("UPDATE series SET tipo = 'normal' WHERE tipo = 'simplificada'");
        DB::statement("ALTER TABLE series MODIFY COLUMN tipo ENUM('normal','rectificativa','abono','proveedor','presupuesto') NOT NULL DEFAULT 'normal'");
    }
};
