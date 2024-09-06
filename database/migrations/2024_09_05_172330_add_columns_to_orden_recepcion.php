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
        Schema::table('orden_recepciones', function (Blueprint $table) {
            $table->boolean('completado')->default(false)->after('forma_pago_id');
            $table->boolean('editar')->default(false)->after('completado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_recepciones', function (Blueprint $table) {
            $table->dropColumn('completado');
            $table->dropColumn('editar');
        });
    }
};
