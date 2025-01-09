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
        Schema::create('pagos_mano_obra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mano_obra_id')->references('id')->on('mano_obra')->onDelete('cascade');
            $table->foreignId('pago_prestamo_id')->references('id')->on('pagos_prestamos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_mano_obra');
    }
};