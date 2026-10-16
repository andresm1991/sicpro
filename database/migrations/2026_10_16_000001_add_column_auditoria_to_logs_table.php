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
        Schema::table('logs', function (Blueprint $table) {
            $table->string('entidad', 60)->nullable()->after('context');
            $table->unsignedBigInteger('entidad_id')->nullable()->after('entidad');
            $table->string('accion', 30)->nullable()->after('entidad_id');
            $table->json('valores_antes')->nullable()->after('accion');
            $table->json('valores_despues')->nullable()->after('valores_antes');
            $table->unsignedBigInteger('user_id')->nullable()->after('valores_despues');
            $table->string('ip', 45)->nullable()->after('user_id');

            $table->index(['entidad', 'entidad_id', 'created_at'], 'logs_entidad_id_fecha_index');
            $table->index(['user_id', 'created_at'], 'logs_user_fecha_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex('logs_entidad_id_fecha_index');
            $table->dropIndex('logs_user_fecha_index');

            $table->dropColumn([
                'entidad',
                'entidad_id',
                'accion',
                'valores_antes',
                'valores_despues',
                'user_id',
                'ip',
            ]);
        });
    }
};
