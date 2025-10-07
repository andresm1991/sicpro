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
            $table->foreignId('cliente_id')->references('id')->on('clientes_ventas')->onDelete('cascade');

            // Etapa Actual del Proceso
            $table->string('etapa_actual')->default('reserva')->comment('reserva, documentacion, escrituracion, desembolso, finalizado');

            // --- ETAPA 1: RESERVA ---
            $table->decimal('valor_reserva', 10, 2);
            $table->string('contrato_firmado_path')->nullable();
            $table->json('cedulas_path')->nullable(); // JSON para guardar rutas de múltiples archivos
            $table->string('comprobante_pago_reserva_path')->nullable();

            // --- ETAPA 2: DOCUMENTACIÓN INICIAL ---
            $table->boolean('visita_perito')->default(false);
            $table->boolean('aprobacion_credito')->default(false);

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