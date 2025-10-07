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
        Schema::create('documentacion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_venta_id')->references('id')->on('proceso_ventas')->onDelete('cascade');
            $table->string('nombre');
            $table->enum('estado', ['pendiente', 'entregado', 'en_revision', 'aprobado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentacion_items');
    }
};