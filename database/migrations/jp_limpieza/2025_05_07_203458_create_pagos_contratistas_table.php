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
        Schema::connection('mysql_jp_limpieza')->create('pagos_contratistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contratista_id')->constrained('contratistas')->onDelete('cascade');
            $table->decimal('monto', 14, 4)->comment('Monto del pago');
            $table->enum('tipo_pago', ['AVANCE', 'LIQUIDACION'])->comment('Tipo de pago: avance o liquidación');
            $table->unsignedBigInteger('forma_pago_id')->comment('Forma de pago: efectivo, cheque, transferencia');
            $table->date('fecha')->comment('Fecha del pago');
            $table->unsignedBigInteger('estado_id')->comment('Estado del pago: pendiente, completado, cancelado');
            $table->text('observaciones')->nullable()->comment('Observaciones sobre el pago');
            $table->unsignedBigInteger('usuario_id')->comment('ID del usuario que realizó el pago');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('pagos_contratistas');
    }
};
