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
        Schema::table('reposicion_tiempos', function (Blueprint $table) {
            $table->unsignedBigInteger('estado_id')->nullable()->after('total');
            $table->foreign('estado_id')->references('id')->on('catalogo_datos')->onDelete('cascade');
            $table->string('detalle')->nullable()->after('estado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reposicion_tiempos', function (Blueprint $table) {
            $table->dropColumn('estado_id');
            $table->dropColumn('detalle');
        });
    }
};
