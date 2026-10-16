<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indice unico de adquisiciones.numero.
     *
     * Los numeros duplicados historicos (36 grupos en sicpro, 28 en jp_limpieza)
     * se pudieron crear porque la columna no tenia indice unico: el codigo
     * calculaba el numero y la base lo aceptaba sin chistar. Ya reparado el
     * historico, este indice convierte cualquier duplicado futuro en un error
     * ruidoso en vez de un duplicado mudo.
     */
    public function up(): void
    {
        // El indice no se puede crear con duplicados presentes. Se falla con un
        // mensaje que dice que correr, en vez del "Duplicate entry" crudo de MySQL,
        // que en un deploy dejaria el estado a mitad de camino sin explicacion.
        $duplicados = DB::table('adquisiciones')
            ->select('numero')
            ->groupBy('numero')
            ->havingRaw('count(*) > 1')
            ->get()
            ->count();

        if ($duplicados > 0) {
            throw new RuntimeException(
                "No se puede crear el indice unico de adquisiciones.numero: quedan {$duplicados} numeros duplicados. "
                . 'Corre primero database/scripts/2026_10_16_renumerar_adquisiciones_duplicadas_sicpro.sql '
                . 'y despues volve a migrar.'
            );
        }

        Schema::table('adquisiciones', function (Blueprint $table) {
            $table->unique('numero', 'adquisiciones_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::table('adquisiciones', function (Blueprint $table) {
            $table->dropUnique('adquisiciones_numero_unique');
        });
    }
};
