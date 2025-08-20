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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->date('fecha_solicitud');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->time('hora_desde');
            $table->time('hora_hasta');
            $table->time('total_tiempo');
            $table->foreignId('tipo_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->foreignId('estado_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->boolean('recuperable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
