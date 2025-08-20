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
        Schema::create('venta_propiedades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion');
            $table->decimal('area', 12, 2);
            $table->string('telefono', 10)->nullable();
            $table->string('correo')->nullable();
            $table->double('latitud');
            $table->double('longitud');
            $table->decimal('precio_venta', 14, 2);
            $table->decimal('precio_por_metros_cuadrados', 14, 2);
            $table->enum('estado', ['DISPONIBLE', 'VENDIDO']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_propiedades');
    }
};