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
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_recepcion_id')->nullable();
            $table->foreignId('producto_id')->references('id')->on('articulos')->onDelete('cascade');
            $table->integer('cantidad');
            $table->integer('cantidad_debaja');
            $table->date('fecha');
            $table->foreignId('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreignId('estado_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->timestamps();

            $table->foreign('orden_recepcion_id')->references('id')->on('orden_recepciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};