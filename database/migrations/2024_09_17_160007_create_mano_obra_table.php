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
        Schema::create('mano_obra', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('semana');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos');
            $table->foreignId('etapa_id')->references('id')->on('catalogo_datos');
            $table->foreignId('tipo_etapa_id')->references('id')->on('catalogo_datos');
            $table->foreignId('usuario_id')->references('id')->on('usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mano_obra');
    }
};
