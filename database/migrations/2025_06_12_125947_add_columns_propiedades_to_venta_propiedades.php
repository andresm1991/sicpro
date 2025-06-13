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
        Schema::table('venta_propiedades', function (Blueprint $table) {
            $table->decimal('frente', 10, 2)->nullable()->after('precio_por_metros_cuadrados');
            $table->decimal('fondo', 10, 2)->nullable()->after('frente');
            $table->unsignedBigInteger('tipo_propiedad_id')->nullable()->after('fondo');
            $table->foreign('tipo_propiedad_id')->references('id')->on('catalogo_datos')->onDelete('cascade');

            $table->string('observaciones')->nullable()->after('tipo_propiedad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta_propiedades', function (Blueprint $table) {
            $table->dropColumn('tipo_propiedad_id');
        });
    }
};