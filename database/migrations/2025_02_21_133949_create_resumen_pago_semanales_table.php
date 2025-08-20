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
        Schema::create('resumen_pago_semanales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->decimal('total', 10, 4);
            $table->foreignId('estado_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumen_pago_semanales');
    }
};
