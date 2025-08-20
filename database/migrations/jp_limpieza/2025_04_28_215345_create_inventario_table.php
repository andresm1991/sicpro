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
        Schema::connection('mysql_jp_limpieza')->create('inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adquisicion_id')->nullable()->comment('ID de la adquisicion para saber si fue ingresado mediante una adquisicion o manual (opcional)');
            $table->foreignId('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->integer('cantidad')->default(0)->comment('Cantidad de producto en inventario');
            $table->integer('cantidad_debaja')->default(0)->comment('Cantidad de producto dado de baja');
            $table->date('fecha_ingreso')->comment('Fecha de ingreso al inventario');
            $table->date('fecha_debaja')->nullable()->comment('Fecha de baja del inventario');
            $table->unsignedBigInteger('usuario_id')->comment('ID del usuario que ingreso el producto al inventario');
            $table->smallInteger('estado')->comment('Estado de producto: 1 al 10');
            $table->timestamps();

            $table->foreign('adquisicion_id')->references('id')->on('adquisiciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('inventario');
    }
};