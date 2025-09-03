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
        Schema::connection('mysql_jp_limpieza')->table('adquisiciones', function (Blueprint $table) {
            $table->boolean('administrativo')->default(false)->after('forma_pago_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->table('adquisiciones', function (Blueprint $table) {
            $table->dropColumn('administrativo');
        });
    }
};