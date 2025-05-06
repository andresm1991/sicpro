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
        Schema::connection('mysql_jp_limpieza')->create('contratistas', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->comment('Número de contrato');
            $table->foreignId('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->unsignedBigInteger('proveedor_id')->comment('ID del proveedor de la base de datos sicpro');
            $table->unsignedBigInteger('categoria_id')->comment('ID del articulos de la base de datos sicpro');
            $table->date('fecha');
            $table->integer('plazo');
            $table->unsignedBigInteger('estado_id')->comment('ID del catalogo_datos de la base de datos sicpro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('contratistas');
    }
};
