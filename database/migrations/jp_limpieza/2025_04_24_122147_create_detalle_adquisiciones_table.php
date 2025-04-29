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
        Schema::connection('mysql_jp_limpieza')->create('detalle_adquisiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adquisicion_id')->constrained('adquisiciones')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->double('cantidad');
            $table->unsignedBigInteger('unidad_medida_id')->nullable()->comment('ID de la unidad de medida de la base de datos sicpro');
            $table->decimal('precio_unitario', 14, 4)->nullable();
            $table->smallInteger('iva')->nullable();
            $table->string('necesidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->dropIfExists('detalle_adquisiciones');
    }
};