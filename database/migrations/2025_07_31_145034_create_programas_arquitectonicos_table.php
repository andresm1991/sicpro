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
        Schema::create('programas_arquitectonicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proforma_id')->nullable();
            $table->unsignedBigInteger('plantilla_id')->nullable();
            $table->string('nombre');
            $table->string('estilo');
            $table->text('urls_referencias')->nullable();
            $table->timestamps();

            $table->foreign('proforma_id')->references('id')->on('proformas_planos')->onDelete('cascade');
            $table->foreign('plantilla_id')->references('id')->on('programas_arquitectonicos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programas_arquitectonicos');
    }
};