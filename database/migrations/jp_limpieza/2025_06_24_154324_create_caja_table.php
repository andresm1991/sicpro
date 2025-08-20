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
        Schema::connection('mysql_jp_limpieza')->create('caja', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('articulo_manual')->nullable();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 14, 4);
            $table->string('descripcion');
            $table->string('referencia')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('origen_type')->nullable(); // Clase del modelo relacionado
            $table->unsignedBigInteger('origen_id')->nullable(); // ID del modelo relacionado
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('caja');
    }
};
