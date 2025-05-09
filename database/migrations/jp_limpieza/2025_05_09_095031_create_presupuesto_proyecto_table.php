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
        Schema::connection('mysql_jp_limpieza')->create('presupuesto_proyecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->onDelete('cascade');
            $table->foreignId('rubro_presupuesto_id')->constrained('rubros_presupuesto')->onDelete('cascade');
            $table->double('cantidad');
            $table->decimal('precio_unitario', 14, 4)->nullable();
            $table->smallInteger('iva')->nullable();
            $table->smallInteger('meses');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('presupuesto_proyecto');
    }
};