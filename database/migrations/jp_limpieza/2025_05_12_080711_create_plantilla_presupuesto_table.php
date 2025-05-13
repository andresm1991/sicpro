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
        Schema::connection('mysql_jp_limpieza')->create('plantilla_presupuesto', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->string('detalle')->nullable();
            $table->string('slug');
            $table->unsignedBigInteger('padre_id')->nullable();
            $table->boolean('activo');
            $table->timestamps();

            $table->foreign('padre_id')->references('id')->on('plantilla_presupuesto')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('plantilla_presupuesto');
    }
};