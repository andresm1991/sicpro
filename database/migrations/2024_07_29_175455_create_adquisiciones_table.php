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
        Schema::create('adquisiciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('numero');
            $table->unsignedBigInteger('proyecto_id')->index();
            $table->unsignedBigInteger('etapa_id')->index();
            $table->unsignedBigInteger('tipo_etapa_id')->index();
            $table->unsignedBigInteger('usuario_id')->index();
            $table->string('estado');
            $table->timestamps();

            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreign('etapa_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->foreign('tipo_etapa_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adquisiciones');
    }
};
