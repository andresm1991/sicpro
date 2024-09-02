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
        Schema::create('adquisiciones_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adquisicion_id')->index();
            $table->unsignedBigInteger('articulo_id')->index();
            $table->integer('cantidad_solicitada');
            $table->integer('cantidad_recibida')->nullable();
            $table->string('necesidad');
            $table->timestamps();

            $table->foreign('adquisicion_id')->references('id')->on('adquisiciones')->onDelete('cascade');
            $table->foreign('articulo_id')->references('id')->on('articulos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adquisiciones_detalle');
    }
};
