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
        Schema::table('adquisiciones_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('unidad_medida_id')->nullable()->after('cantidad_recibida');
            $table->double('valor')->nullable()->after('unidad_medida_id');

            $table->foreign('unidad_medida_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adquisiciones_detalle', function (Blueprint $table) {
            $table->dropColumn('unidad_medida_id');
            $table->dropColumn('valor');
        });
    }
};