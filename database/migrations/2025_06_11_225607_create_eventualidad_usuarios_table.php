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
        Schema::create('eventualidad_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->references('id')->on('solicitudes')->onDelete('cascade');
            $table->foreignId('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventualidad_usuarios');
    }
};