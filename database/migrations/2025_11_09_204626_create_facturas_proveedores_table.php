<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facturas_proveedor', function (Blueprint $table) {
            $table->id();

            // Proveedor (en tu tabla clientes, marcados con proveedor=1)
            $table->foreignId('cliente_id')->constrained('clientes');

            // Serie (usa la tabla series)
            $table->foreignId('serie_id')->constrained('series');
            $table->unsignedBigInteger('numero')->nullable();
            $table->string('numero_completo')->nullable()->unique(); 
            // Número que trae la factura del proveedor
            $table->string('numero_proveedor', 100);

            // Fechas
            $table->date('fecha'); // fecha de la factura (expedición/emisión)

            // Concepto libre opcional (para búsqueda interna)
            $table->text('concepto')->nullable();

            // Totales (cabecera)
            $table->decimal('base', 13, 2)->default(0);
            $table->decimal('iva_total', 13, 2)->default(0);
            $table->decimal('irpf_total', 13, 2)->default(0);
            $table->decimal('total', 13, 2)->default(0);
            $table->char('moneda', 3)->default('EUR');

            $table->timestamps();

            // Evita duplicados por proveedor + serie + número del proveedor
            $table->unique(['proveedor_id', 'serie_id', 'numero_proveedor'], 'fp_unique_prov_serie_num');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas_proveedor');
    }
};
