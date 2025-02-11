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
        Schema::create('cronograma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreignId('rubro_id')->references('id')->on('rubros_presupuesto')->onDelete('cascade');
            $table->foreignId('etapa_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->smallInteger('semana');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cronograma');
    }
};