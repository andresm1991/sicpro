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
        Schema::connection('mysql_jp_limpieza')->create('adquisiciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('numero');
            $table->foreignId('proyecto_id')->constrained('proyectos')->onDelete('cascade');
            $table->unsignedBigInteger('proveedor_id')->comment('ID del proveedor de la base de datos sicpro');
            $table->unsignedBigInteger('tipo_id')->comment('ID del tipo de adquisicion de la base de datos sicpro en catalogo de datos');
            $table->string('estado')->default('pendiente')->comment('Estado de la adquisicion');
            $table->string('nro_factura')->nullable();
            $table->string('archivo')->nullable();
            $table->unsignedBigInteger('forma_pago_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('adquisiciones');
    }
};
