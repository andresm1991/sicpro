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
        Schema::create('pagos_orden_trabajo_contratista', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('contratista_id')->references('id')->on('contratistas')->onDelete('cascade');
            $table->enum('tipo_pago', ['AVANCE', 'LIQUIDACION']);
            $table->enum('forma_pago', ['EFECTIVO', 'TRANSFERENCIA', 'CHEQUE']);
            $table->double('valor');
            $table->string('detalle');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_orden_trabajo_contratista');
    }
};
