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
        Schema::create('detalle_contratistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contratista_id')->references('id')->on('contratistas');
            $table->foreignId('articulo_id')->references('id')->on('articulos');
            $table->smallInteger('cantidad');
            $table->foreignId('unidad_medida_id')->references('id')->on('catalogo_datos');
            $table->double('valor_unitario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_contratistas');
    }
};
