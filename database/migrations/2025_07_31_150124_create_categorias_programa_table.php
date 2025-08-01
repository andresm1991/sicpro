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
        Schema::create('categorias_programa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('es_exterior')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias_programa');
    }
};