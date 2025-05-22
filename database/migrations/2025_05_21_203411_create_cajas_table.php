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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->decimal('saldo_nuevo', 12, 2)->comment('Ingreso manual del usuario');
            $table->decimal('saldo_inicial', 12, 2)->comment('Saldo anterior + saldo_nuevo');
            $table->decimal('saldo_actual', 12, 2)->default(0)->comment('Saldo actual calculado');
            $table->boolean('activa')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
