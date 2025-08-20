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
        Schema::table('contratistas', function (Blueprint $table) {
            $table->smallInteger('numero_casas')->nullable()->after('estado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratistas', function (Blueprint $table) {
            $table->dropColumn('numero_casas');
        });
    }
};