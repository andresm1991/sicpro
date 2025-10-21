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
        Schema::table('adquisiciones', function (Blueprint $table) {
            $table->foreignId('tipo_costo_id')->nullable()->after('tipo_etapa_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adquisiciones', function (Blueprint $table) {
            $table->dropForeign(['tipo_costo_id']);
            $table->dropColumn('tipo_costo_id');
        });
    }
};