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
        Schema::create('mano_obra', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('proyecto_id')->references('id')->on('proyectos');
            $table->foreignId('proveedor_id')->references('id')->on('proveedores');
            $table->foreignId('articulo_id')->references('id')->on('articulos');
            $table->foreignId('etapa_id')->references('id')->on('catalogo_datos');
            $table->foreignId('tipo_etapa_id')->references('id')->on('catalogo_datos');
            $table->foreignId('usuario_id')->references('id')->on('usuarios');
            $table->enum('jornada', ['COMPLETA', 'MEDIO TIEMPO']);
            $table->double('valor');
            $table->double('adicional')->nullable()->default(0);
            $table->double('descuento')->nullable()->default(0);
            $table->string('detalle_adicional')->nullable();
            $table->string('detalle_descuento')->nullable();
            $table->string('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mano_obra');
    }
};
