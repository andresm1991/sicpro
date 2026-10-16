<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\AdquisicionDetalle;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Contratista;
use App\Models\DetalleContratista;
use App\Models\DetalleManoObra;
use App\Models\ManoObra;
use App\Models\PagoManoObra;
use App\Models\PagoOrdenTrabajoContratista;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * El Total General del reporte global de adquisiciones tiene que cuadrar con
 * los gastos del Reporte Balance de Gerencia para el mismo proyecto: los dos
 * miden lo mismo y con la misma base.
 *
 * Reporte global: adquisiciones completadas + contratistas PAGADOS + mano de
 * obra de los periodos con pago. Balance: exactamente lo mismo.
 */
class ReporteGlobalCuadraConBalanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Proyecto $proyecto;
    private CatalogoDato $etapa;
    private CatalogoDato $tipoEtapa;
    private CatalogoDato $tipoEtapaAdquisicion;
    private CatalogoDato $unidadMedida;
    private Articulo $articulo;
    private Proveedor $proveedor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['usuario' => 'usuario_cuadre_reporte_test']);

        $estadoEjecucion = CatalogoDato::create(['descripcion' => 'Ejecucion', 'slug' => 'estados.proyectos.ejecucion', 'activo' => true]);
        $this->etapa = CatalogoDato::create(['descripcion' => 'Etapa cuadre', 'slug' => 'etapa-cuadre', 'activo' => true]);
        $this->tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa cuadre', 'slug' => 'tipo-etapa-cuadre', 'activo' => true]);
        // El reporte global agrupa las adquisiciones por el slug de su tipo de etapa:
        // en produccion solo existen 'meteriales.herramientas' y 'servicios'.
        $this->tipoEtapaAdquisicion = CatalogoDato::create(['descripcion' => 'Materiales y herramientas', 'slug' => 'meteriales.herramientas', 'activo' => true]);
        $this->unidadMedida = CatalogoDato::create(['descripcion' => 'Unidad cuadre', 'slug' => 'unidad-cuadre', 'activo' => true]);
        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo cuadre', 'slug' => 'categoria-articulo-cuadre', 'activo' => true]);
        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor cuadre', 'slug' => 'categoria-proveedor-cuadre', 'activo' => true]);

        $this->proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $this->etapa->id,
            'nombre_proyecto' => 'Proyecto cuadre reporte',
            'nombre_propietario' => 'Propietario cuadre',
            'telefono' => '0999999999',
            'correo' => 'cuadre@example.com',
            'tipo_proyecto_id' => $this->etapa->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoEjecucion->id,
        ]);

        $this->articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'descripcion' => 'Articulo cuadre',
            'activo' => true,
        ]);

        $this->proveedor = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0000000001',
            'direccion' => 'Direccion cuadre',
            'razon_social' => 'Proveedor cuadre',
        ]);
    }

    /** Adquisicion completada: 2 x 100 con IVA 10% => 220. */
    private function crearAdquisicion(?Proyecto $proyecto = null): void
    {
        $proyecto ??= $this->proyecto;

        $adquisicion = Adquisicion::create([
            'fecha' => '2025-01-10',
            'numero' => 'ADQ-CUADRE-' . $proyecto->id,
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $this->etapa->id,
            'tipo_etapa_id' => $this->tipoEtapaAdquisicion->id,
            'usuario_id' => $this->user->id,
            'estado' => 'Completado',
            'tipo_adquisicion' => 'operativo',
        ]);

        AdquisicionDetalle::create([
            'adquisicion_id' => $adquisicion->id,
            'articulo_id' => $this->articulo->id,
            'cantidad_solicitada' => 2,
            'unidad_medida_id' => $this->unidadMedida->id,
            'valor' => 100,
            'iva' => 10,
            'necesidad' => 'obra',
        ]);
    }

    /** Contratista: 1 x 1000 contratado, 400 pagado. */
    private function crearContratista(): Contratista
    {
        $contratista = Contratista::create([
            'fecha' => '2025-01-15',
            'plazo_semanas' => 4,
            'proveedor_id' => $this->proveedor->id,
            'articulo_id' => $this->articulo->id,
            'proyecto_id' => $this->proyecto->id,
            'etapa_id' => $this->etapa->id,
            'tipo_etapa_id' => $this->tipoEtapa->id,
            'usuario_id' => $this->user->id,
            'estado_id' => $this->etapa->id,
            'numero_casas' => 1,
        ]);

        DetalleContratista::create([
            'contratista_id' => $contratista->id,
            'articulo_id' => $this->articulo->id,
            'cantidad' => 1,
            'unidad_medida_id' => $this->unidadMedida->id,
            'valor_unitario' => 1000,
        ]);

        PagoOrdenTrabajoContratista::create([
            'fecha' => '2025-02-01',
            'contratista_id' => $contratista->id,
            'tipo_pago' => 'AVANCE',
            'forma_pago' => 'EFECTIVO',
            'valor' => 400,
            'detalle' => 'Avance de obra',
            'pagado' => true,
        ]);

        return $contratista;
    }

    /** Mano de obra: un periodo de 500 con pago registrado. */
    private function crearManoObra(): void
    {
        $manoObra = ManoObra::create([
            'semana' => 1,
            'fecha_inicio' => '2025-01-06',
            'fecha_fin' => '2025-01-12',
            'proyecto_id' => $this->proyecto->id,
            'etapa_id' => $this->etapa->id,
            'tipo_etapa_id' => $this->tipoEtapa->id,
            'usuario_id' => $this->user->id,
        ]);

        DetalleManoObra::create([
            'fecha' => '2025-01-12',
            'mano_obra_id' => $manoObra->id,
            'proveedor_id' => $this->proveedor->id,
            'articulo_id' => $this->articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 500,
        ]);

        // En el sistema real el pago de mano de obra entra por un prestamo al
        // trabajador: el pago de mano de obra siempre cuelga de un pago de
        // prestamo (pago_prestamo_id es NOT NULL).
        $prestamo = Prestamo::create([
            'trabajador_id' => $this->proveedor->id,
            'fecha_solicitud' => '2025-01-06',
            'monto' => 500,
            'saldo' => 500,
            'plazo' => 4,
            'estado_id' => $this->etapa->id,
            'motivo' => 'Semana de trabajo',
        ]);

        $pagoPrestamo = PagoPrestamo::create([
            'prestamo_id' => $prestamo->id,
            'fecha_pago' => '2025-01-12',
            'monto_pagado' => 500,
            'monto_programado' => 500,
            'estado_id' => $this->etapa->id,
            'metodo_pago_id' => $this->etapa->id,
        ]);

        PagoManoObra::create([
            'mano_obra_id' => $manoObra->id,
            'pago_prestamo_id' => $pagoPrestamo->id,
        ]);
    }

    /** Proyecto todavia no iniciado: el balance lo tenia fuera de sus filas. */
    private function crearProyectoPendiente(): Proyecto
    {
        $estadoPendiente = CatalogoDato::create([
            'descripcion' => 'Pendiente',
            'slug' => 'estados.proyectos.pendiente',
            'activo' => true,
        ]);

        return Proyecto::create([
            'catalogo_proyecto_id' => $this->etapa->id,
            'nombre_proyecto' => 'Proyecto cuadre pendiente',
            'nombre_propietario' => 'Propietario cuadre pendiente',
            'telefono' => '0999999998',
            'correo' => 'cuadre-pendiente@example.com',
            'tipo_proyecto_id' => $this->etapa->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoPendiente->id,
        ]);
    }

    private function requestReporte(?string $fechas = null, ?Proyecto $proyecto = null): Request
    {
        return Request::create('/reportes/visulizar-reporte-adquisiciones', 'POST', [
            'tipo_reporte' => 'global',
            'proyecto' => ($proyecto ?? $this->proyecto)->id,
            'fechas' => $fechas,
        ]);
    }

    private function requestBalance(?string $fechas = null, ?Proyecto $proyecto = null): Request
    {
        return Request::create('/gerencia/reporte/generar-reporte-balance', 'POST', [
            'tipo_reporte' => 'balance_proyecto',
            'proyecto' => ($proyecto ?? $this->proyecto)->id,
            'fechas' => $fechas,
        ]);
    }

    private function dataReporte(?string $fechas = null, ?Proyecto $proyecto = null): array
    {
        return Adquisicion::reporteGlobalAdquisiciones($this->requestReporte($fechas, $proyecto));
    }

    private function subtotalReporte(?string $fechas = null, ?Proyecto $proyecto = null): float
    {
        return Adquisicion::totalesReporteGlobal($this->dataReporte($fechas, $proyecto), 0)['subtotal'];
    }

    private function totalGastosBalance(?string $fechas = null, ?Proyecto $proyecto = null): float
    {
        $balance = Proyecto::dataReporteBalance($this->requestBalance($fechas, $proyecto));

        return Proyecto::totalesBalanceReporte($balance)['total_gastos'];
    }

    public function test_el_balance_incluye_los_proyectos_en_estado_pendiente(): void
    {
        $pendiente = $this->crearProyectoPendiente();
        $this->crearAdquisicion($pendiente);

        // El reporte siempre lo mostro; ahora el balance tiene que mostrar lo mismo.
        $this->assertEqualsWithDelta(220.0, $this->subtotalReporte(null, $pendiente), 0.0001);
        $this->assertEqualsWithDelta(220.0, $this->totalGastosBalance(null, $pendiente), 0.0001);

        // Y aparece como fila en el balance global.
        $global = Proyecto::dataReporteBalance(Request::create('/', 'POST', ['tipo_reporte' => 'balance_global']));
        $fila = collect($global)->firstWhere('proyecto_id', $pendiente->id);

        $this->assertNotNull($fila, 'El balance global tiene que listar el proyecto pendiente.');
        $this->assertEqualsWithDelta(220.0, (float) $fila['total_gastos'], 0.0001);
    }

    public function test_el_total_del_reporte_global_cuadra_con_los_gastos_del_balance(): void
    {
        $this->crearAdquisicion();
        $this->crearContratista();
        $this->crearManoObra();

        // 220 (adquisicion con IVA) + 400 (pago del contratista) + 500 (mano de obra) = 1120
        $this->assertEqualsWithDelta(1120.0, $this->subtotalReporte(), 0.0001);
        $this->assertEqualsWithDelta(1120.0, $this->totalGastosBalance(), 0.0001);
        $this->assertEqualsWithDelta($this->totalGastosBalance(), $this->subtotalReporte(), 0.0001);
    }

    public function test_los_contratistas_del_reporte_aportan_lo_pagado_y_no_lo_contratado(): void
    {
        $this->crearContratista();

        // Contratado 1000, pagado 400: el reporte y el balance suman 400.
        $this->assertEqualsWithDelta(400.0, $this->subtotalReporte(), 0.0001);
        $this->assertEqualsWithDelta(400.0, $this->totalGastosBalance(), 0.0001);
    }

    public function test_la_mano_de_obra_del_reporte_aporta_el_valor_pagado(): void
    {
        $this->crearManoObra();

        $this->assertEqualsWithDelta(500.0, $this->subtotalReporte(), 0.0001);
        $this->assertEqualsWithDelta(500.0, $this->totalGastosBalance(), 0.0001);
    }

    public function test_con_rango_de_fechas_que_no_incluye_nada_los_dos_reportes_dan_cero(): void
    {
        $this->crearAdquisicion();
        $this->crearContratista();
        $this->crearManoObra();

        // 2024: las tres fuentes son de enero/febrero de 2025.
        $rango = '01/01/2024 - 01/31/2024';

        $this->assertEqualsWithDelta(0.0, $this->subtotalReporte($rango), 0.0001);
        $this->assertEqualsWithDelta(0.0, $this->totalGastosBalance($rango), 0.0001);

        // El listado tambien se acota: ninguna etapa queda con filas.
        $this->assertSame([], $this->dataReporte($rango)['data']);
    }

    public function test_con_rango_de_fechas_el_contratista_suma_solo_los_pagos_del_rango(): void
    {
        $this->crearAdquisicion();
        $this->crearContratista();
        $this->crearManoObra();

        // Febrero 2025: solo cae el pago del contratista (01/02/2025).
        $rango = '02/01/2025 - 02/28/2025';

        $this->assertEqualsWithDelta(400.0, $this->subtotalReporte($rango), 0.0001);
        $this->assertEqualsWithDelta(400.0, $this->totalGastosBalance($rango), 0.0001);

        // El periodo de mano de obra (06/01 al 12/01) queda fuera y no se lista.
        $this->assertSame([], $this->dataReporte($rango)['data']['Etapa cuadre']['mano_obra']);
    }

    public function test_con_rango_de_fechas_la_mano_de_obra_aporta_los_periodos_que_solapan(): void
    {
        $this->crearAdquisicion();
        $this->crearContratista();
        $this->crearManoObra();

        // 10/01 al 20/01/2025: entra la adquisicion (10/01) y el periodo de
        // mano de obra (06/01 al 12/01), que arranca antes pero solapa.
        $rango = '01/10/2025 - 01/20/2025';

        $this->assertEqualsWithDelta(720.0, $this->subtotalReporte($rango), 0.0001);
        $this->assertEqualsWithDelta(720.0, $this->totalGastosBalance($rango), 0.0001);
    }

    public function test_un_pago_no_pagado_del_contratista_no_aporta(): void
    {
        $this->crearContratista();
        PagoOrdenTrabajoContratista::query()->update(['pagado' => false]);

        $this->assertEqualsWithDelta(0.0, $this->subtotalReporte(), 0.0001);
        $this->assertEqualsWithDelta(0.0, $this->totalGastosBalance(), 0.0001);
    }

    public function test_mano_de_obra_sin_fila_de_pago_no_aporta(): void
    {
        $this->crearManoObra();
        PagoManoObra::query()->delete();

        $this->assertEqualsWithDelta(0.0, $this->subtotalReporte(), 0.0001);
        $this->assertEqualsWithDelta(0.0, $this->totalGastosBalance(), 0.0001);
    }

    public function test_una_adquisicion_no_completada_no_aporta(): void
    {
        $this->crearAdquisicion();
        Adquisicion::query()->update(['estado' => 'Pendiente']);

        $this->assertEqualsWithDelta(0.0, $this->subtotalReporte(), 0.0001);
        $this->assertEqualsWithDelta(0.0, $this->totalGastosBalance(), 0.0001);
    }

    public function test_con_rango_de_fechas_el_saldo_del_contratista_sigue_siendo_la_deuda_real(): void
    {
        $contratista = $this->crearContratista();
        // Segundo pago, fuera del rango que se va a consultar.
        PagoOrdenTrabajoContratista::create([
            'fecha' => '2025-03-15',
            'contratista_id' => $contratista->id,
            'tipo_pago' => 'LIQUIDACION',
            'forma_pago' => 'TRANSFERENCIA',
            'valor' => 100,
            'detalle' => 'Liquidacion parcial',
            'pagado' => true,
        ]);

        $rango = '02/01/2025 - 02/28/2025';

        // Al total del reporte entra solo el pago del rango.
        $this->assertEqualsWithDelta(400.0, $this->subtotalReporte($rango), 0.0001);
        $this->assertEqualsWithDelta(400.0, $this->totalGastosBalance($rango), 0.0001);

        $fila = $this->dataReporte($rango)['data']['Etapa cuadre']['contratista'][0];
        $this->assertEqualsWithDelta(1000.0, $fila['total_contratado'], 0.0001);
        $this->assertEqualsWithDelta(400.0, $fila['pagos'], 0.0001);
        // 1000 - (400 + 100): la deuda real no depende del rango consultado.
        $this->assertEqualsWithDelta(500.0, $fila['saldo'], 0.0001);

        // Sin rango, el saldo sigue siendo el mismo.
        $filaSinRango = $this->dataReporte()['data']['Etapa cuadre']['contratista'][0];
        $this->assertEqualsWithDelta(500.0, $filaSinRango['pagos'], 0.0001);
        $this->assertEqualsWithDelta(500.0, $filaSinRango['saldo'], 0.0001);
    }
}
