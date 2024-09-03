<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpcionesCamposFormularioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opciones_campos_formulario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campo_formulario_id')->index();
            $table->text('valor');
            $table->text('texto');
            $table->timestamps();

            $table->foreign('campo_formulario_id')->references('id')->on('campos_formulario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opciones_campos_formulario');
    }
}