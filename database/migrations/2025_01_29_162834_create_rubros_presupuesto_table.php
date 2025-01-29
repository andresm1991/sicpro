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
        Schema::create('rubros_presupuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_presupuesto_id')->references('id')->on('categorias_presupuesto')->onDelete('cascade');
            $table->string('nombre');
            $table->foreignId('unidad_medida_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->decimal('valor_unitario');
            $table->boolean('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubros_presupuesto');
    }
};
