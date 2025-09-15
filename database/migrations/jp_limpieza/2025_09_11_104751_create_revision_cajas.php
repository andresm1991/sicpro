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
        Schema::connection('mysql_jp_limpieza')->create('revision_cajas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_revision_inicio')->unique();
            $table->date('fecha_revision_fin')->unique();
            $table->unsignedBigInteger('usuario_id');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('revision_cajas');
    }
};