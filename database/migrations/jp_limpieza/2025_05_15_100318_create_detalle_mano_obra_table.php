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
        Schema::connection('mysql_jp_limpieza')->create('detalle_mano_obra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mano_obra_id')->constrained('mano_obra')->onDelete('cascade');
            $table->unsignedBigInteger('proveedor_id');
            $table->decimal('sueldo', 14, 4);
            $table->decimal('horas_extras', 14, 4)->nullable();
            $table->decimal('total_ganado', 14, 4);
            $table->decimal('fondos', 14, 4)->nullable();
            $table->decimal('decimo_tercero', 14, 4)->nullable();
            $table->decimal('decimo_cuarto', 14, 4)->nullable();
            $table->decimal('total_ingreso', 14, 4);
            $table->decimal('iess', 14, 4)->nullable();
            $table->decimal('atrasos_faltas', 14, 4)->nullable();
            $table->decimal('anticipos', 14, 4)->nullable();
            $table->decimal('prestamo_iess', 14, 4)->nullable();
            $table->decimal('quincena', 14, 4)->nullable();
            $table->decimal('prestamo_jp', 14, 4)->nullable();
            $table->decimal('total_descuentos', 14, 4);
            $table->decimal('total_recibir', 14, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('detalle_mano_obra');
    }
};