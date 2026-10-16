<?php

namespace App\Console\Commands;

use App\Models\Log;
use Illuminate\Console\Command;

class PurgarLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:purgar {--dias= : Cantidad de dias a conservar (sobreescribe config auditoria.retencion_dias)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina los registros de la tabla logs mas viejos que la retencion configurada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dias = $this->option('dias') !== null
            ? (int) $this->option('dias')
            : (int) config('auditoria.retencion_dias', 365);

        $corte = now()->subDays($dias);

        $eliminados = Log::where('created_at', '<', $corte)->delete();

        $this->info("Purga completada: {$eliminados} registro(s) de logs eliminados (mas viejos que {$dias} dia(s)).");

        return 0;
    }
}
