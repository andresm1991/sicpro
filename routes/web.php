<?php

use App\Models\CatalogoDato;
use App\Models\AdquisicionDetalle;
use App\Models\PresupuestoProyecto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\ManoObraController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\CronogramaController;
use App\Http\Controllers\GenerarPdfController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ReporteriaController;
use App\Http\Controllers\AdquisicionController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\CatalogoDatoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\RecuperacionTiempoController;
use App\Http\Controllers\ResumenPagoSemanalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm']);

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    require base_path('routes/modules/gerencia.php');
    require base_path('routes/modules/proyectos.php');
    require base_path('routes/modules/sistema.php');
    require base_path('routes/modules/administrativo.php');
    require base_path('routes/modules/export_pdf.php');
    require base_path('routes/modules/reporteria.php');
    require base_path('routes/modules/solicitudes.php');
    require base_path('routes/modules/agenda.php');
    require base_path('routes/modules/proforma.php');
    require base_path('routes/modules/jp_limpieza.php');


    // Perfil Usuario
    Route::group(['prefix' => 'mi-perfil', 'as' => 'perfil.'], function () {
        Route::get('/', [UserController::class, 'perfil'])->name('show');
        Route::put('/{user}/actualizar', [UserController::class, 'updatePerfil'])->name('update');
    });

    //** PETICIONES AJAX **
    Route::put('/pago_orden_trabajo/{pago}', [AdministrativoController::class, 'pagoOrdenTrabajo']);
    Route::get('/rubros-presupuesto', [PresupuestoController::class, 'getAjaxRubrosPresupuesto']);
    Route::put('/actualizar-costo-indirecto/{proyecto}', [PresupuestoController::class, 'putAjaxCostoIndirecto']);
    Route::get('/actividades-cronograma', [CronogramaController::class, 'getAjaxActividades']);
    Route::get('/tiempo-permisos', [RecuperacionTiempoController::class, 'getAjaxTiempoPermisosUsuario']);

    // Eliminar  rubros del presupuesto
    Route::delete('/eliminar-rubro-presupesto/{rubro}', [PresupuestoController::class, 'destroyAjaxRubroPresupuesto']);
    Route::delete('/eliminar-categoria-presupesto/{categoria}', [PresupuestoController::class, 'destroyAjaxCategoriaPresupuesto']);
    Route::get('/filtrar-rubros-presupuesto', [PresupuestoController::class, 'filtrarRubrosPresupuesto']);

    Route::get('/bancos', [CatalogoDatoController::class, 'getBancos']);
    Route::get('/tipo-cuenta', [CatalogoDatoController::class, 'getTipoCuentas']);
    Route::get('/articulos-proveedor', [ArticuloController::class, 'getArticulosProveedor']);
    Route::delete('/orden-trabajo/eliminar/{id}', [ContratistaController::class, 'eliminarOrdenTrabajo']);
    Route::get('/proveedores', [ProveedorController::class, 'getProveedores']);
    Route::get('/forma-pago-prestamo', [CatalogoDatoController::class, 'getFormasPagoPrestamo']);

    // Start Push Notification==========================================================
    Route::view('push-notification', 'PushNotification.Index');
    Route::post('save-push-notification-sub', [PushNotificationController::class, 'saveSubscription']);
    Route::post('send-push-notification', [PushNotificationController::class, 'sendNotification']);
    // End Push Notification==========================================================


    //** RUTAS NOTIFICACION */
    Route::group(['prefix' => 'notificaciones', 'as' => 'notificacion.'], function () {
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::post('/leer-notificacion', [NotificacionController::class, 'leerNotificacion'])->name('leer');
        Route::post('/marcar-todos-leido', [NotificacionController::class, 'leerTodasNotificacion']);
    });
    Route::get('/orde-pago-mano-obra/{mano_obra}/{pago}/view', [GenerarPdfController::class, 'planificacionManoObraPDF'])->name('pago.mano.obra');
    //** FIN RUTAS NOTIFICACIONES */

    Route::get('/no-access', function () {
        return view('errors.no-access');
    })->name('no.access');
});