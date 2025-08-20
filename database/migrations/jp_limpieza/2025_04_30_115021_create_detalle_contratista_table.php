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
        Schema::connection('mysql_jp_limpieza')->create('detalle_contratista', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contratista_id')->constrained('contratistas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad')->default(0)->comment('Cantidad de producto en inventario');
            $table->unsignedBigInteger('unidad_medida_id')->comment('ID del catalogo_datos de la base de datos sicpro');
            $table->decimal('precio_unitario', 14, 4)->comment('Precio unitario del producto');
            $table->integer('iva')->default(0);
            $table->decimal('total', 14, 4)->comment('Total del producto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('detalle_contratista');
    }
};
