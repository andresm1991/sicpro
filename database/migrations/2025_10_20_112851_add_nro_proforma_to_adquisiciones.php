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
        Schema::table('adquisiciones', function (Blueprint $table) {
            $table->string('nro_proforma')->nullable()->after('factura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adquisiciones', function (Blueprint $table) {
            $table->dropColumn('nro_proforma');
        });
    }
};