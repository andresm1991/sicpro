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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->references('id')->on('proveedores');
            $table->date('fecha_solicitud');
            $table->date('fecha_aprobacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo', 10, 2);
            $table->decimal('interes', 5, 2)->default(0);
            $table->smallInteger('plazo');
            $table->foreignId('estado_id')->references('id')->on('catalogo_datos');
            $table->string('motivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
