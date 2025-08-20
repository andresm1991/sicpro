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
        Schema::create('presupuesto_proyecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreignId('rubro_presupuesto_id')->references('id')->on('rubros_presupuesto')->onDelete('cascade');
            $table->double('cantidad');
            $table->decimal('valor_unitario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_proyecto');
    }
};
