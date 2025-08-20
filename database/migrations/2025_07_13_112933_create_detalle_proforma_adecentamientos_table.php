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
        Schema::create('detalle_proforma_adecentamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_adecentamiento_id')->references('id', 'dpa_proforma_adecentamiento_fk')->on('proforma_adecentamientos')->onDelete('cascade');
            $table->foreignId('producto_id')->references('id')->on('proforma_productos')->onDelete('cascade');
            $table->decimal('cantidad', 10, 2); // Cantidad del producto
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
        Schema::dropIfExists('detalle_proforma_adecentamientos');
    }
};
