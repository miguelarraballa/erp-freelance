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
        Schema::create('presupuesto_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->unsignedInteger('orden')->default(1);
            $table->boolean('producto')->default(0);
            $table->text('concepto');
            $table->decimal('cantidad', 12, 3)->default(1);
            $table->decimal('precio_unitario', 13, 4)->default(0);
            $table->decimal('descuento_pct', 5, 2)->default(0); // 0..100
            $table->foreignId('impuesto_id')->nullable()->constrained('impuestos')->nullOnDelete(); // tipo aplicado en la línea
            $table->decimal('base_linea', 13, 2)->default(0);
            $table->decimal('iva_linea', 13, 2)->default(0);
            $table->decimal('irpf_linea', 13, 2)->default(0);
            $table->decimal('total_linea', 13, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_lineas');
    }
};
