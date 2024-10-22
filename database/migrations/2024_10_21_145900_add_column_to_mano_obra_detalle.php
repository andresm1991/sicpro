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
        Schema::table('mano_obra', function (Blueprint $table) {
            $table->unsignedBigInteger('actividad_id')->nullable()->after('tipo_etapa_id');
            $table->foreign('actividad_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mano_obra', function (Blueprint $table) {
            $table->dropColumn('actividad_id');
        });
    }
};
