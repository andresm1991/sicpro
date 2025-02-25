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
        Schema::create('detalle_resumen_pago_semanales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resumen_pago_semanal_id')->references('id')->on('resumen_pago_semanales')->onDelete('cascade'); 
            $table->string('descripcion');
            $table->decimal('monto', 10, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_resumen_pago_semanales');
    }
};