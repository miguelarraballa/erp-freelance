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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serie_id')->constrained('series');
            $table->unsignedBigInteger('numero')->nullable(); // se asigna al emitir
            $table->string('numero_completo')->nullable()->unique(); // prefijo+numero+sufijo
            
            $table->foreignId('cliente_id')->constrained('clientes'); // tu tabla clientes
            $table->text('datos_facturacion')->nullable();

            $table->date('fecha')->nullable();
            $table->date('vencimiento')->nullable();
            $table->enum('estado', ['borrador','emitida','cobrada','anulada'])->default('borrador');
            $table->enum('tipo', ['normal','rectificativa','abono'])->default('normal');
            $table->string('verificacion_hash')->nullable(); // hash AEAT VeriFactu (cuando toque)

            // Totales
            $table->decimal('base', 13, 2)->default(0);
            $table->decimal('iva_total', 13, 2)->default(0);
            $table->decimal('irpf_total', 13, 2)->default(0);
            $table->decimal('total', 13, 2)->default(0);

            $table->char('moneda', 3)->default('eur');
            $table->text('notas')->nullable();

            // Rectificativa
            $table->foreignId('rectifica_id')->nullable()->constrained('facturas')->nullOnDelete();

            // Trazabilidad de autoría (opcional)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Unicidad por serie/numero si se quiere reforzar además de numero_completo único:
            $table->unique(['serie_id','numero']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
