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
        Schema::create('solicitudes_descuento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_prestamo_id')->references('id')->on('pagos_prestamos')->onDelete('cascade');
            $table->foreignId('tipo_solicitud_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->decimal('monto_solicitado', 10, 2)->nullable();
            $table->text('motivo')->nullable();
            $table->foreignId('estado_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_descuento');
    }
};