<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indice unico de adquisiciones.numero en la base de JP Limpieza.
     *
     * Esta carpeta no la corre `php artisan migrate`: hay que ejecutarla aparte con
     *   php artisan migrate --path=database/migrations/jp_limpieza --database=mysql_jp_limpieza
     * Ver odd/tasks/numeros-duplicados-reparacion.md.
     */
    public function up(): void
    {
        $duplicados = DB::connection('mysql_jp_limpieza')
            ->table('adquisiciones')
            ->select('numero')
            ->groupBy('numero')
            ->havingRaw('count(*) > 1')
            ->get()
            ->count();

        if ($duplicados > 0) {
            throw new RuntimeException(
                "No se puede crear el indice unico de adquisiciones.numero: quedan {$duplicados} numeros duplicados. "
                . 'Corre primero database/scripts/2026_10_16_renumerar_adquisiciones_duplicadas_jp_limpieza.sql '
                . 'y despues volve a migrar.'
            );
        }

        Schema::connection('mysql_jp_limpieza')->table('adquisiciones', function (Blueprint $table) {
            $table->unique('numero', 'adquisiciones_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_jp_limpieza')->table('adquisiciones', function (Blueprint $table) {
            $table->dropUnique('adquisiciones_numero_unique');
        });
    }
};
