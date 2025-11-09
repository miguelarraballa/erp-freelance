<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facturas_proveedor_impuestos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('factura_proveedor_id')
              ->constrained('facturas_proveedor')
              ->cascadeOnDelete();

            // Desglose por tipo de IVA (21, 10, 4, etc.)
            $t->decimal('porcentaje', 5, 2);   // p.ej. 21.00
            $t->decimal('base', 13, 2);
            $t->decimal('cuota', 13, 2);

            $t->timestamps();

            // (Opcional) evitar duplicar el mismo porcentaje para la misma factura
            $t->unique(['factura_proveedor_id', 'porcentaje'], 'fpi_unique_factura_pct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas_proveedor_impuestos');
    }
};