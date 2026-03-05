<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woo_pedidos_importados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas_woo')->cascadeOnDelete();
            $table->unsignedBigInteger('woo_order_id');   // ID del pedido en WooCommerce
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('woo_status');                 // estado original del pedido en WooCommerce
            $table->timestamps();

            $table->unique(['tienda_id', 'woo_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woo_pedidos_importados');
    }
};
