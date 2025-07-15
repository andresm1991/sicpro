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
        Schema::create('proforma_adecentamientos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('fecha');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->text('observaciones')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('estado_id')->constrained('catalogo_datos')->onDelete('cascade');
            $table->string('validez')->default('15 dias'); // Validez de la proforma, por ejemplo: 15 días, 30 días, etc.
            $table->string('forma_pago')->default('Contado'); // Forma de pago, por ejemplo: contado, crédito, etc.
            $table->string('plazo_entrega')->default('15 días laborables'); // Plazo de entrega, por ejemplo: inmediato, 7 días, 15 días, etc.
            $table->decimal('subtotal', 10, 2);
            $table->smallInteger('descuento')->default(0);
            $table->smallInteger('iva')->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_adecentamientos');
    }
};