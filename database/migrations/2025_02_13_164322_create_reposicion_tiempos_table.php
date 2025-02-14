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
        Schema::create('reposicion_tiempos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_desde');
            $table->time('hora_hasta');
            $table->time('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reposicion_tiempos');
    }
};
