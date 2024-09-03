<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogoDatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogo_datos', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->text('detalle')->nullable();
            $table->string('slug', 150)->nullable();
            $table->unsignedBigInteger('padre_id')->nullable();
            $table->boolean('activo')->default(1);
            $table->timestamps();

            $table->foreign('padre_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogo_datos');
    }
}
