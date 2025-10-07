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
        Schema::create('escrituracion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_venta_id')->constrained('proceso_ventas')->onDelete('cascade');
            $table->string('nombre');
            $table->boolean('completado')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escrituracion_items');
    }
};