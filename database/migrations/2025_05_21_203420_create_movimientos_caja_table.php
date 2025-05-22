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
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->references('id')->on('cajas')->onDelete('cascade');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 14, 4);
            $table->string('descripcion');
            $table->string('referencia')->nullable();
            $table->foreignId('user_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->string('origen_type')->nullable(); // Clase del modelo relacionado
            $table->unsignedBigInteger('origen_id')->nullable(); // ID del modelo relacionado
            $table->timestamps();

            $table->index(['origen_type', 'origen_id', 'proveedor_id']);

            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
