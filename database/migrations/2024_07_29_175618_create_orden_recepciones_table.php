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
        Schema::create('orden_recepciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('adquisicion_id')->index();
            $table->unsignedBigInteger('proveedor_id')->index();
            $table->unsignedBigInteger('forma_pago_id')->index();
            $table->timestamps();

            $table->foreign('adquisicion_id')->references('id')->on('adquisiciones')->onDelete('cascade');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('cascade');
            $table->foreign('forma_pago_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_recepciones');
    }
};
