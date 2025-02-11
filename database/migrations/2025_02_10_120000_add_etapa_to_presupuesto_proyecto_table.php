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
        Schema::table('presupuesto_proyecto', function (Blueprint $table) {
            $table->unsignedBigInteger('etapa_id')->nullable()->after('valor_unitario');
            $table->foreign('etapa_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuesto_proyecto', function (Blueprint $table) {
            $table->dropColumn('etapa_id');
        });
    }
};