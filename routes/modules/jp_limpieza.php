<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Models\JPLimpieza\PresupuestoProyecto;
use App\Http\Controllers\JPLimpieza\ManoObraController;
use App\Http\Controllers\JPLimpieza\ProyectoController;
use App\Http\Controllers\JPLimpieza\AdquisicionController;
use App\Http\Controllers\JPLimpieza\CajaController;
use App\Http\Controllers\JPLimpieza\ContratistaController;
use App\Http\Controllers\JPLimpieza\PagoContratistaController;
use App\Http\Controllers\JPLimpieza\PresupuestoProyectoController;
use App\Http\Controllers\JPLimpieza\ReporteController;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;

Route::group(['prefix' => 'jp-limpieza', 'as' => 'jp.limpieza.'], function () {
    Route::get('/', function () {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => ''],
        ];
        return view('jp_limpieza.home', compact('breadcrumbs'));
    })->name('index');

    Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');
    //->middleware('can:jp.limpieza.proyectos.index');
    Route::get('/nuevo-proyecto', [ProyectoController::class, 'create'])->name('proyectos.create');
    Route::post('/guardar-proyecto', [ProyectoController::class, 'store'])->name('proyectos.store');

    Route::group(['prefix' => 'proyecto', 'as' => 'proyectos.'], function () {
        Route::get('/{proyecto}', [ProyectoController::class, 'show'])->name('show');
        Route::get('/{proyecto}/editar', [ProyectoController::class, 'edit'])->name('edit');
        Route::put('/{proyecto}', [ProyectoController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProyectoController::class, 'destroy']);
    });

    //** ADQUISICIONES */
    Route::group(['prefix' => 'adquisiciones', 'as' => 'adquisiciones.'], function () {
        Route::get('/{proyecto}', [AdquisicionController::class, 'index'])->name('index');
        Route::get('/{proyecto}/{tipo_adquisicion}', [AdquisicionController::class, 'adquisiciones'])->name('tipo.adquisicion');
        Route::get('/{proyecto}/{tipo_adquisicion}/nuevo', [AdquisicionController::class, 'create'])->name('create');
        Route::post('/{proyecto}/{tipo_adquisicion}/guardar', [AdquisicionController::class, 'store'])->name('store');
        Route::get('/{proyecto}/{tipo_adquisicion}/editar/{adquisicion}', [AdquisicionController::class, 'edit'])->name('edit');
        Route::put('/{proyecto}/{tipo_adquisicion}/editar/{adquisicion}', [AdquisicionController::class, 'update'])->name('update');
        Route::delete('/{proyecto}/eliminar/{adquisicion}', [AdquisicionController::class, 'destroy'])->name('destroy');
        Route::get('/{proyecto}/{tipo_adquisicion}/buscar', [AdquisicionController::class, 'buscar']);
    });

    //** ADQUISICIONES ADMINISTRATIVAS */
    Route::group(['prefix' => 'adquisiciones-administrativas', 'as' => 'adquisiciones.administrativas.'], function () {
        Route::get('/', [AdquisicionController::class, 'indexAdministrativo'])->name('index');
        Route::get('/create', [AdquisicionController::class, 'createAdministrativo'])->name('create');
        Route::get('/editar/{adquisicion}', [AdquisicionController::class, 'editAdministrativo'])->name('edit');
        Route::delete('/eliminar/{adquisicion}', [AdquisicionController::class, 'destroy']);
        Route::get('/buscar', [AdquisicionController::class, 'buscar']);
    });

    //** CONTRATISTAS */
    Route::group(['prefix' => 'contratistas/{proyecto}', 'as' => 'contratistas.'], function () {
        Route::get('/', [ContratistaController::class, 'index'])->name('index');
        Route::get('/nuevo', [ContratistaController::class, 'create'])->name('create');
        Route::post('/guardar', [ContratistaController::class, 'store'])->name('store');
        Route::get('/{contratista}/editar', [ContratistaController::class, 'edit'])->name('edit');
        Route::put('/{contratista}', [ContratistaController::class, 'update'])->name('update');
        Route::delete('/eliminar-contratista/{id}', [ContratistaController::class, 'destroy']);
        Route::get('/buscar', [ContratistaController::class, 'buscar']);

        Route::get('/pagos/{contratista}', [PagoContratistaController::class, 'pagos'])->name('pagos');
        Route::get('/pagos/{contratista}/nuevo-pago', [PagoContratistaController::class, 'crear'])->name('pagos.create');
        Route::post('/pagos/{contratista}/guardar-pago', [PagoContratistaController::class, 'store'])->name('pagos.store');
        Route::get('/pagos/{contratista}/editar-pago/{pago}', [PagoContratistaController::class, 'edit'])->name('pagos.edit');
        Route::put('/pagos/{contratista}/editar-pago/{pago}', [PagoContratistaController::class, 'update'])->name('pagos.update');
        Route::delete('/pagos/{contratista}/eliminar-pago/{pago}', [PagoContratistaController::class, 'destroy']);
        Route::get('/pagos/{contratista}/buscar', [PagoContratistaController::class, 'buscar']);
    });
    //** PRESUPUESTO */
    Route::group(['prefix' => 'presupuesto/{proyecto}', 'as' => 'presupuesto.'], function () {
        Route::get('/', [PresupuestoProyectoController::class, 'index'])->name('index');
        Route::get('/rubros-presupuesto', [PresupuestoProyectoController::class, 'ajaxRubros']);
        Route::post('/guardar-rubro', [PresupuestoProyectoController::class, 'AjaxStoreRubro']);
        Route::post('/guadar-presupuesto', [PresupuestoProyectoController::class, 'store'])->name('store');
        Route::put('/actualizar-rubro/{rubro}', [PresupuestoProyectoController::class, 'updateRubroDescripcion']);
        Route::put('/actualizar-categoria/{categoria}', [PresupuestoProyectoController::class, 'updateCategoriaDescripcion']);
    });

    //**  MANO DE OBRA */
    Route::group(['prefix' => 'mano-obra/{proyecto}', 'as' => 'mano.obra.'], function () {
        Route::get('/', [ManoObraController::class, 'index'])->name('index');
        Route::get('/nueva-planificacion', [ManoObraController::class, 'create'])->name('create');
        Route::post('/guardar-planificacion', [ManoObraController::class, 'store'])->name('store');
        Route::get('/editar-planificacion/{mano_obra}', [ManoObraController::class, 'edit'])->name('edit');
        Route::put('/actualizar-planificacion/{mano_obra}', [ManoObraController::class, 'update'])->name('update');
        Route::delete('/eliminar-mano-obra/{mano_obra}', [ManoObraController::class, 'destroy']);
    });

    //** MANO DE OBRA ADMINISTRATIVO */
    Route::group(['prefix' => 'mano-de-obra-administrativa', 'as' => 'mano.obra.administrativa.'], function () {
        Route::get('/', [ManoObraController::class, 'indexAdministrativo'])->name('index');
        Route::get('/create', [ManoObraController::class, 'createAdministrativo'])->name('create');
        Route::get('/editar/{mano_obra}', [ManoObraController::class, 'editAdministrativo'])->name('edit');
        Route::get('/buscar', [ManoObraController::class, 'buscar']);
    });

    //** CAJA */
    Route::group(['prefix' => 'caja', 'as' => 'caja.'], function () {
        Route::get('/', [CajaController::class, 'index'])->name('index');
        Route::post('/guardar', [CajaController::class, 'guardarMovimiento'])->name('store');
    });

    //** REPORTES */
    Route::group(['prefix' => 'reportes', 'as' => 'reporte.'], function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/caja', [ReporteController::class, 'caja'])->name('caja');
        Route::post('/visulizar-reporte-caja', [ReporteController::class, 'visualizarReporteCaja']);
        Route::post('/guardar-revision-caja', [ReporteController::class, 'guardarRevisionCaja']);

        //** ADQUISICIONES */
        Route::get('/adquisiciones', [ReporteController::class, 'adquisiciones'])->name('adquisiciones');
        Route::post('/visualizar-reporte-adquisiciones', [ReporteController::class, 'visualizarReporteAdquisiciones']);
        Route::get('/filtro-reporte-adquisiciones', [ReporteController::class, 'filtroReporteAdquisiciones']);
    });
});
