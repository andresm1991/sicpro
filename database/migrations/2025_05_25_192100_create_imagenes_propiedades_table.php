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
        Schema::create('imagenes_propiedades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_propiedad_id')->references('id')->on('venta_propiedades')->onDelete('cascade');
            $table->string('file_name');
            $table->string('path_file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes_propiedades');
    }
};