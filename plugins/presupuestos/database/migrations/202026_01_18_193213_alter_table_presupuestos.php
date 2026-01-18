<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->enum('estado', ['borrador','emitido','facturado','aceptado','no-aceptado'])->default('borrador')->change();
        });
    }

    public function down(): void
    {
        //Pass
    }
};
