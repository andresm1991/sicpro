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
        Schema::create('pagos_prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->references('id')->on('prestamos')->onDelete('cascade');
            $table->date('fecha_pago');
            $table->decimal('monto_pagado', 10, 2);
            $table->decimal('monto_programado', 10, 2);
            $table->foreignId('estado_id')->references('id')->on('catalogo_datos');
            $table->foreignId('metodo_pago_id')->references('id')->on('catalogo_datos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_prestamos');
    }
};