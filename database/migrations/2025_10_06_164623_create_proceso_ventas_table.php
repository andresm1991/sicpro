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
        Schema::create('proceso_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->smallInteger('unidad');
            $table->decimal('valor_venta', 10, 2);
            $table->foreignId('cliente_id')->references('id')->on('clientes_ventas')->onDelete('cascade');

            // Etapa Actual del Proceso
            $table->string('etapa_actual')->default('reserva')->comment('reserva, documentacion, escrituracion, desembolso, finalizado');

            // --- ETAPA 1: RESERVA ---
            $table->decimal('valor_reserva', 10, 2);
            $table->string('contrato_firmado_path')->nullable();
            //$table->json('cedulas_path')->nullable(); // JSON para guardar rutas de múltiples archivos
            $table->string('cedula_path')->nullable();
            $table->string('comprobante_pago_reserva_path')->nullable();

            // --- ETAPA 3: PERITAJE Y APROBACION DE CREDITO ---
            $table->boolean('visita_perito')->default(false);
            $table->boolean('aprobacion_credito')->default(false);
            $table->string('observaciones_peritaje')->nullable();

            // --- ETAPA 4: ESCRITURACION ---
            $table->decimal('valor_saldo_reserva', 10, 2)->nullable();
            $table->string('comprobante_pago_saldo_reserva_path')->nullable();

            // --- ETAPA 4: DESEMBOLSO ---
            $table->decimal('monto_desembolsado', 10, 2)->nullable();
            $table->text('observaciones_desembolso')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proceso_ventas');
    }
};