<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         Schema::create('presupuestos_facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->nullable()->constrained('presupuestos')->cascadeOnDelete();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->cascadeOnDelete(); 
            $table->timestamps();

            // Evita duplicados por proveedor + serie + número del proveedor
            $table->unique(['presupuesto_id', 'factura_id'], 'fp_unique_presupuesto_factura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos_facturas');
    }
};
