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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->text('direccion')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->boolean('activo')->default(true); // Indica si el cliente está activo o no
            $table->string('tipo_cliente', 50)->nullable(); // Tipo de cliente, por ejemplo: particular, empresa, etc.
            $table->string('ruc', 13)->nullable(); // RUC del cliente, si aplica
            $table->string('contacto')->nullable(); // Nombre del contacto principal del cliente
            $table->string('telefono_contacto', 50)->nullable(); // Teléfono del contacto principal
            $table->string('email_contacto', 100)->nullable(); // Email del contacto principal
            $table->text('observaciones')->nullable(); // Observaciones adicionales sobre el cliente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};