<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_proveedor_id')->index();
            $table->string('documento', 13);
            $table->string('razon_social');
            $table->string('telefono', 50)->nullable();
            $table->string('correo')->nullable();
            $table->string('direccion');
            $table->unsignedBigInteger('banco_id')->index()->nullable();
            $table->unsignedBigInteger('tipo_cuenta_id')->index()->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->text('observacion')->nullable();
            $table->smallInteger('calificacion')->nullable();
            $table->timestamps();

            $table->foreign('categoria_proveedor_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->foreign('banco_id')->references('id')->on('bancos')->onDelete('cascade');
            $table->foreign('tipo_cuenta_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proveedores');
    }
}