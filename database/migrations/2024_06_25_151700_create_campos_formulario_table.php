<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCamposFormularioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campos_formulario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formulario_id')->index();
            $table->string('lable');
            $table->string('tipo', 50);
            $table->boolean('requerido');
            $table->integer('orden');
            $table->text('instrucciones')->nullable();
            $table->timestamps();

            $table->foreign('formulario_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campos_formulario');
    }
}