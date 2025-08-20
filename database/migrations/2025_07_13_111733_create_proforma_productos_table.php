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
        Schema::create('proforma_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('catalogo_datos')->onDelete('cascade');
            $table->string('nombre')->unique(); // Nombre del producto
            $table->text('descripcion')->nullable(); // Descripción del producto
            $table->decimal('precio', 10, 2); // Precio del producto
            $table->smallInteger('iva')->default(0); // Porcentaje de IVA aplicado al producto
            $table->decimal('precio_final', 10, 2); // Precio final del producto
            $table->unsignedBigInteger('unidad_medida_id')->nullable(); // Unidad de medida del producto
            $table->boolean('activo')->default(true); // Indica si el producto está activo o no
            $table->timestamps();

            $table->foreign('unidad_medida_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_productos');
    }
};
