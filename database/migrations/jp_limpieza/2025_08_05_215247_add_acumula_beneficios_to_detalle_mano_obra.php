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
        Schema::connection('mysql_jp_limpieza')->table('detalle_mano_obra', function (Blueprint $table) {
            $table->boolean('acumula_beneficios')->default(false)->after('total_ganado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->table('detalle_mano_obra', function (Blueprint $table) {
            $table->dropColumn('acumula_beneficios');
        });
    }
};