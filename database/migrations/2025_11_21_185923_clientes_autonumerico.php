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
         Schema::create('clientes_autonumerico', function (Blueprint $t) {
            $t->id();
            $t->string("tipo")->unique();
            $t->unsignedBigInteger('siguiente_numero')->default(1);
            $t->timestamps();            
        });

        DB::table('clientes_autonumerico')->insertOrIgnore([
            ['tipo' => 'cliente', 'siguiente_numero' => '1'],
            ['tipo' => 'proveedor', 'siguiente_numero' => '1'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
