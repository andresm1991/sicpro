<?php

namespace App\Console\Commands;

use App\Models\BudgetTracker;
use App\Models\Proyecto;
use App\Models\CategoriaPresupuesto;
use App\Models\RubroPresupuesto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BudgetSeedTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:seed-trackers {--batch=1000 : Number of records per batch}
                            {--projects= : Comma-separated project IDs (default: all active)}
                            {--dry-run : Preview what would be created without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed BudgetTracker records from existing proyecto expense data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch');
        $dryRun = $this->option('dry-run');
        $projectIds = $this->option('projects');

        $this->info("Starting BudgetTracker seed...");
        $this->info("Batch size: {$batchSize}");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No data will be saved");
        }

        // Get projects to seed
        $proyectos = $this->getProyectos($projectIds);
        
        if ($proyectos->isEmpty()) {
            $this->error("No projects found to seed");
            return 1;
        }

        $this->info("Found {$proyectos->count()} projects to process");

        $totalCreated = 0;
        $progressBar = $this->output->createProgressBar($proyectos->count());
        $progressBar->start();

        foreach ($proyectos as $proyecto) {
            $trackers = $this->generateTrackersForProyecto($proyecto);
            
            if ($dryRun) {
                $totalCreated += count($trackers);
            } else {
                $created = $this->insertTrackers($trackers, $batchSize);
                $totalCreated += $created;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully processed {$totalCreated} BudgetTracker records");

        return 0;
    }

    /**
     * Get proyectos to seed.
     */
    protected function getProyectos($projectIds)
    {
        $query = Proyecto::whereHas('adquisiciones')
            ->orWhereHas('mano_obra')
            ->orWhereHas('contratista');

        if ($projectIds) {
            $ids = array_map('intval', explode(',', $projectIds));
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    /**
     * Generate BudgetTracker records for a proyecto.
     */
    protected function generateTrackersForProyecto($proyecto)
    {
        $trackers = [];

        // Get existing expense data for this proyecto
        $adquisicionesTotal = $this->getAdquisicionesTotal($proyecto->id);
        $manoObraTotal = $this->getManoObraTotal($proyecto->id);
        $contratistaTotal = $this->getContratistaTotal($proyecto->id);

        // Get categorias and rubros for this proyecto
        $categoriaRubros = $this->getCategoriaRubros($proyecto->id);

        foreach ($categoriaRubros as $cr) {
            $trackers[] = [
                'proyecto_id' => $proyecto->id,
                'categoria_presupuesto_id' => $cr['categoria_id'],
                'rubro_presupuesto_id' => $cr['rubro_id'],
                'budget_total' => $cr['valor_total'] ?? 0,
                'expenses_adq' => ($adquisicionesTotal / max($categoriaRubros->count(), 1)),
                'expenses_mo' => ($manoObraTotal / max($categoriaRubros->count(), 1)),
                'expenses_contr' => ($contratistaTotal / max($categoriaRubros->count(), 1)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $trackers;
    }

    /**
     * Get total adquisiciones for a proyecto.
     */
    protected function getAdquisicionesTotal($proyectoId)
    {
        $proyecto = Proyecto::find($proyectoId);
        if (!$proyecto) return 0;

        return $proyecto->adquisiciones
            ->where('estado', 'Completado')
            ->sum(function ($item) {
                return $item->adquisiciones_detalle->sum(function ($detail) {
                    $iva = $detail->iva ?? 0;
                    return ($detail->cantidad_solicitada * $detail->valor) * (1 + $iva / 100);
                });
            });
    }

    /**
     * Get total mano de obra for a proyecto.
     */
    protected function getManoObraTotal($proyectoId)
    {
        return DB::table('detalle_mano_obra')
            ->whereHas('mano_obra', function ($query) use ($proyectoId) {
                $query->where('proyecto_id', $proyectoId);
            })
            ->whereHas('mano_obra.pago_mano_obra')
            ->sum(DB::raw('COALESCE(valor, 0) + COALESCE(adicional, 0) - COALESCE(descuento, 0)'));
    }

    /**
     * Get total contratista for a proyecto.
     */
    protected function getContratistaTotal($proyectoId)
    {
        return DB::table('pago_orden_trabajo_contratista')
            ->whereHas('contratista', function ($query) use ($proyectoId) {
                $query->where('proyecto_id', $proyectoId);
            })
            ->where('pagado', true)
            ->sum('valor');
    }

    /**
     * Get categorias and rubros for a proyecto.
     */
    protected function getCategoriaRubros($proyectoId)
    {
        return DB::table('presupuesto_proyectos')
            ->join('rubros_presupuesto', 'presupuesto_proyectos.rubro_presupuesto_id', '=', 'rubros_presupuesto.id')
            ->where('presupuesto_proyectos.proyecto_id', $proyectoId)
            ->select(
                'rubros_presupuesto.categoria_presupuesto_id as categoria_id',
                'rubros_presupuesto.id as rubro_id',
                DB::raw('SUM(presupuesto_proyectos.cantidad * presupuesto_proyectos.valor_unitario) as valor_total')
            )
            ->groupBy('rubros_presupuesto.categoria_presupuesto_id', 'rubros_presupuesto.id')
            ->get();
    }

    /**
     * Insert trackers in batches.
     */
    protected function insertTrackers(array $trackers, int $batchSize)
    {
        $chunks = array_chunk($trackers, $batchSize);
        $created = 0;

        foreach ($chunks as $chunk) {
            try {
                BudgetTracker::upsert(
                    $chunk,
                    ['proyecto_id', 'categoria_presupuesto_id', 'rubro_presupuesto_id'],
                    ['budget_total', 'expenses_adq', 'expenses_mo', 'expenses_contr', 'updated_at']
                );
                $created += count($chunk);
            } catch (\Exception $e) {
                $this->warn("Error inserting batch: " . $e->getMessage());
            }
        }

        return $created;
    }
}
