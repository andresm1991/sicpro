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
            $table->decimal('aporte_patronal', 10, 2)->nullable()->default(0)->after('iess');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->table('detalle_mano_obra', function (Blueprint $table) {
            $table->dropColumn('aporte_patronal');
        });
    }
};
