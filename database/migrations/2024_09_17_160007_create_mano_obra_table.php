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
            $table->foreignId('proveedor_id')->references('id')->on('proveedores'); 
            $table->foreignId('articulo_id')->references('id')->on('articulos'); 
            $table->enum('jornada', ['COMPLETA', 'MEDIO TIEMPO']);
            $table->double('valor')->default(0);
            $table->double('adicional')->default(0);
            $table->double('descuento')->default(0);
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
        Schema::create('mano_obra', function (Blueprint $table) {
            Schema::dropIfExists('mano_obra');
        });
    }
};
