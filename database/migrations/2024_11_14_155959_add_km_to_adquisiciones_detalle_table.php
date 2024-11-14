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
            $table->double('kilometraje')->nullable()->after('necesidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adquisiciones_detalle', function (Blueprint $table) {
            $table->dropColumn('kilometraje');
        });
    }
};
