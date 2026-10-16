<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla logs propia de la base de JP Limpieza (S4: "Una tabla por base").
     *
     * Replica las columnas e indices de la tabla principal `logs` (ver
     * database/migrations/2024_09_02_173328_create_logs_table.php y
     * database/migrations/2026_10_16_000001_add_column_auditoria_to_logs_table.php):
     * la base jp_limpieza no se puede migrar con `migrate --database` (tiene
     * 102 migraciones pendientes, su base se creo por dumps), asi que esta
     * carpeta se corre aparte con
     *   php artisan migrate --path=database/migrations/jp_limpieza --database=mysql_jp_limpieza
     * y para la base ya desplegada se aplica a mano
     * database/scripts/2026_10_16_create_logs_jp_limpieza.sql.
     */
    public function up(): void
    {
        Schema::connection('mysql_jp_limpieza')->create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // Nivel de log: info, warning, error, etc.
            $table->text('message'); // Mensaje del log
            $table->json('context')->nullable(); // Contexto adicional
            $table->string('entidad', 60)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->string('accion', 30)->nullable();
            $table->json('valores_antes')->nullable();
            $table->json('valores_despues')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['entidad', 'entidad_id', 'created_at'], 'logs_entidad_id_fecha_index');
            $table->index(['user_id', 'created_at'], 'logs_user_fecha_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('logs');
    }
};
