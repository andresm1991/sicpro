<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProyectosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalogo_proyecto_id')->index();
            $table->string('nombre_proyecto');
            $table->string('nombre_propietario');
            $table->string('ubicacion')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 10);
            $table->string('correo');
            $table->unsignedBigInteger('tipo_proyecto_id')->index();
            $table->double('area_lote')->nullable();
            $table->double('area_construccion')->nullable();
            $table->integer('numero_unidades')->nullable();
            $table->double('area_lote_unidad')->nullable();
            $table->double('area_construccion_unidad')->nullable();
            $table->double('presupuesto_total')->nullable();
            $table->double('presupuesto_unidad')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_finalizacion')->nullable();
            $table->text('observacion')->nullable();
            $table->string('portada');
            $table->timestamps();

            $table->foreign('tipo_proyecto_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->foreign('catalogo_proyecto_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proyectos');
    }
}
