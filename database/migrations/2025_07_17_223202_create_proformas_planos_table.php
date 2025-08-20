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
        Schema::create('proformas_planos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('fecha');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('ubicacion_lote')->nullable();
            $table->double('area_lote')->nullable();
            $table->decimal('presupuesto', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->text('incluye')->nullable();
            $table->text('plazo_ejecucion')->nullable();
            $table->foreignId('estado_id')->constrained('catalogo_datos')->onDelete('cascade');
            $table->text('forma_pago')->nullable();
            $table->text('abono')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->smallInteger('descuento')->default(0);
            $table->smallInteger('iva')->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proformas_planos');
    }
};