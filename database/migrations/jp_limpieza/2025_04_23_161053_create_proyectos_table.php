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
        Schema::connection('mysql_jp_limpieza')->create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_proyecto');
            $table->string('entidad');
            $table->string('telefono', 10)->nullable();
            $table->decimal('metros_contratado');
            $table->decimal('precio_por_metro');
            $table->smallInteger('tiempo_contratado')->comment('Tiempo contratado en meses');
            $table->date('fecha_inicio');
            $table->date('fecha_finalizacion');
            $table->string('archivo_portada')->nullable();
            $table->string('archivo_orden_compra')->nullable();
            $table->string('archivo_acta_final')->nullable();
            $table->string('observacion')->nullable();
            $table->string('estado')->default('activo')->comment('Estado del proyecto: activo, inactivo, finalizado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('proyectos');
    }
};
