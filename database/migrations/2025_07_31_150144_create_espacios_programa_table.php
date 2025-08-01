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
        Schema::create('espacios_programa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained('programas_arquitectonicos')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias_programa');
            $table->string('espacio')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->text('actividades')->nullable();
            $table->text('mobiliario')->nullable();
            $table->integer('usuario')->default(0);
            $table->decimal('m2', 8, 2)->default(0.00);
            $table->text('observaciones')->nullable();
            $table->text('link_ref')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('espacios_programa');
    }
};