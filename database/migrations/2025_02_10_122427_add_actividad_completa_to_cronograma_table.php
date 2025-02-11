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
        Schema::table('cronograma', function (Blueprint $table) {
            $table->boolean('completado')->default(false)->after('semana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cronograma', function (Blueprint $table) {
            $table->dropColumn('completado');
        });
    }
};