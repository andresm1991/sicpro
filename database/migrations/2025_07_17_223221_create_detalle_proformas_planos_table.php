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
        Schema::create('detalle_proformas_planos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_plano_id')->references('id', 'dpa_proforma_plano_fk')->on('proformas_planos')->onDelete('cascade');
            $table->foreignId('producto_id')->references('id')->on('proforma_productos')->onDelete('cascade');
            $table->integer('area');
            $table->decimal('precio_unitario', 10, 2); // Precio unitario del producto
            $table->decimal('total', 10, 2); // Total del producto (subtotal + IVA)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_proformas_planos');
    }
};