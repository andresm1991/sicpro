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
        Schema::table('proceso_ventas', function (Blueprint $table) {
            $table->string('observaciones_peritaje')->nullable()->after('aprobacion_credito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proceso_ventas', function (Blueprint $table) {
            $table->dropColumn('observaciones_peritaje');
        });
    }
};