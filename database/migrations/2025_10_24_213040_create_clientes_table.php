<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('razon_social')->nullable();
            $table->string('nif', 20)->nullable()->unique();
            $table->string('direccion')->nullable();
            $table->string('cp', 10)->nullable();
            $table->string('ciudad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->default('España');
            $table->string('contacto_nombre')->nullable();
            $table->string('contacto_email')->nullable();
            $table->string('contacto_telefono', 20)->nullable();
            $table->boolean('cliente')->default(true);
            $table->boolean('proveedor')->default(false);
            $table->boolean('irpf')->default(false);
            $table->string('iban', 34)->nullable();
            $table->string('email_facturacion')->nullable();
            $table->string('telefono_facturacion', 20)->nullable();
            $table->string('codigo_cliente')->unique()->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); 
            //Para mostrar al crear los selects
            $table->string('mostrar')->virtualAs(
                "CONCAT(COALESCE(razon_social, ''), IF(nombre IS NULL OR nombre = '', '', CONCAT(' (', nombre, ')')))"
            );
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};