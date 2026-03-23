<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anexos_rgpd', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            // Snapshot datos del cliente
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_nif')->nullable();
            $table->string('cliente_direccion')->nullable();
            $table->string('cliente_email')->nullable();
            $table->string('cliente_firmante')->nullable();
            $table->string('cliente_cargo')->nullable();

            // Servicio
            $table->text('descripcion_servicio')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->string('duracion_acceso')->default('1 año');

            // Accesos (JSON array de claves)
            $table->json('accesos')->nullable();
            $table->string('accesos_otros')->nullable();

            // Observaciones
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });

        // Añadir nombre_comercial a emisores si no existe
        if (!Schema::hasColumn('emisores', 'nombre_comercial')) {
            Schema::table('emisores', function (Blueprint $table) {
                $table->string('nombre_comercial')->nullable()->after('nombre');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('anexos_rgpd');

        if (Schema::hasColumn('emisores', 'nombre_comercial')) {
            Schema::table('emisores', function (Blueprint $table) {
                $table->dropColumn('nombre_comercial');
            });
        }
    }
};
