<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // La columna `cantidad` era SMALLINT, por lo que MySQL redondeaba
        // cualquier valor decimal al insertar/actualizar. Se cambia a DOUBLE
        // para permitir cantidades con decimales (consistente con valor_unitario).
        DB::statement('ALTER TABLE detalle_contratistas MODIFY cantidad DOUBLE NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE detalle_contratistas MODIFY cantidad SMALLINT NOT NULL');
    }
};
