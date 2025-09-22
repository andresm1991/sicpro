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
        Schema::create('clientes_ventas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('documento', 13)->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->boolean('activo')->default(true); // Indica si el cliente está activo o no
            $table->text('observaciones')->nullable(); // Observaciones adicionales sobre el cliente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_ventas');
    }
};