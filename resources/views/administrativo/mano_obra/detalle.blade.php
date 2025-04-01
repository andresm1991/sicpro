@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="col-md-8">
                                <h5>Semana: {{ $mano_obra->semana }}</h5>
                                <h6>{{ dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin) }}</h6>
                            </div>

                            <div class="col-md-2 ">
                                @php
                                    $pagoIds = collect($detalle_mano_obra['detalle']) // Accedemos al array 'detalle'
                                        ->pluck('prestamo') // Extraemos solo la clave 'prestamo'
                                        ->flatten(1) // Aplanamos el array en un solo nivel
                                        ->pluck('pago_id') // Extraemos solo los `pago_id`
                                        ->filter() // Eliminamos valores nulos o vacíos
                                        ->values(); // Reindexamos los valores
                                @endphp

                                @if ($tipo != 'completo')
                                    {!! Form::open([
                                        'route' => ['administrativo.mano.obra.registrar.pago'],
                                        'class' => 'form-horizontal',
                                        'autocomplete' => 'off',
                                        'enctype' => 'multipart/form-data',
                                    ]) !!}

                                    <input type="hidden" name="pago_ids" value="{{ $pagoIds }}">
                                    <input type="hidden" name="mano_obra" value="{{ $mano_obra->id }}">
                                    <button class="btn btn-dark btn-options btn-block">Generar
                                        Pago</button>
                                    {{ Form::close() }}
                                @else
                                @endif
                            </div>

                            <div class="col-md-2 ">
                                <a href="{{ route('pdf.planificacion.mano.obra', $mano_obra->id) }}"
                                    class="btn btn-dark btn-options btn-block" target="__blak">exportar PDF</a>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    <div class="row">
                        <div class="col-md-7 col-12">
                            <div class="form-group row">
                                {{ Form::label('', 'Proyecto', ['class' => 'col-sm-2 col-form-label']) }}
                                <div class="col-sm-10">
                                    {{ Form::text('proyecto', $mano_obra->proyecto->nombre_proyecto, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-12">
                            <div class="form-group row">
                                {{ Form::label('', 'Etapa', ['class' => 'col-sm-2 col-form-label']) }}
                                <div class="col-sm-10">
                                    {{ Form::text('etapa', $mano_obra->etapa->descripcion, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (strtolower($mano_obra->tipo_etapa->descripcion) == 'acabados')
                        <div class="row">
                            <div class="col-md-7 col-12">
                                <div class="form-group row">
                                    {{ Form::label('', 'Actividad', ['class' => 'col-sm-2 col-form-label']) }}
                                    <div class="col-sm-10">
                                        {{ Form::text('etapa', $mano_obra->actividad->descripcion, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover tabla-custom" id="tabla-planificacion">
                            <thead>
                                <tr>
                                    <th class="th-wrap text-fontsize-12 align-middle">Nro</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Apellidos y Nombres</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Cargo</th>
                                    <th class="th-wrap text-fontsize-12 text-center align-middle">L</th>
                                    <th class="th-wrap text-fontsize-12 text-center align-middle">M</th>
                                    <th class="th-wrap text-fontsize-12 text-center align-middle">M</th>
                                    <th class="th-wrap text-fontsize-12 text-center align-middle">J</th>
                                    <th class="th-wrap text-fontsize-12 text-center align-middle">V</th>
                                    <th class="th-wrap text-fontsize-12 text-center align-middle">S</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Adicionales</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Detalle Adicionales</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">TOTAL</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Descuento</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Detalle Descuento</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Descuento Prestamo</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Liquido a Recibir</th>
                                    <th class="th-wrap text-fontsize-12 align-middle">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $lastName = '';
                                    $rowspan = 1;
                                    $index = 1;
                                    $totalDias = 0;
                                    $liquidoRecibirTotal = 0;

                                    $totalAdicionales = 0;
                                    $totalPagoDias = 0;
                                    $totalDescuentos = 0;
                                    $totalRecibir = 0;
                                    $totalPagosPrestamos = 0;
                                @endphp

                                @foreach ($detalle_mano_obra['detalle'] as $detalle)
                                    @php
                                        // Verificar si el nombre es diferente al anterior
                                        $isFirstRowForName = $detalle['nombre'] !== $lastName;

                                        if ($isFirstRowForName) {
                                            // Contar cuántas filas pertenecen al mismo nombre
                                            $rowspan = collect($detalle_mano_obra['detalle'])
                                                ->where('nombre', $detalle['nombre'])
                                                ->unique('cargo') // Asegura que se cuenten solo cargos distintos
                                                ->count();

                                            // Calcular el total de días trabajados + adicionales para todas las filas de este trabajador
                                            $totalDias = collect($detalle_mano_obra['detalle'])
                                                ->where('nombre', $detalle['nombre'])
                                                ->sum(function ($d) {
                                                    return array_sum($d['dias']) + $d['total_adicional'];
                                                });

                                            // Calcular el total del líquido a recibir (total - descuento)
                                            $liquidoRecibirTotal = collect($detalle_mano_obra['detalle'])
                                                ->where('nombre', $detalle['nombre'])
                                                ->sum(function ($d) {
                                                    return $d['total'] -
                                                        $d['total_descuento'] -
                                                        collect($d['prestamo'])->sum('pagos');
                                                });
                                        }

                                        // Sumar los valores para los totales generales
                                        $totalAdicionales += $detalle['total_adicional'];
                                        $totalPagoDias += array_sum($detalle['dias']) + $detalle['total_adicional'];
                                        $totalDescuentos += $detalle['total_descuento'];
                                        $totalPagosPrestamos += collect($detalle['prestamo'])->sum('pagos');
                                        $totalRecibir +=
                                            $detalle['total'] -
                                            $detalle['total_descuento'] -
                                            collect($detalle['prestamo'])->sum('pagos');

                                    @endphp
                                    <tr>
                                        @if ($isFirstRowForName)
                                            <td rowspan="{{ $rowspan }}" class="align-middle text-fontsize-12">
                                                {{ $index }}
                                            </td>
                                            <td rowspan="{{ $rowspan }}" class="align-middle text-fontsize-12">
                                                {{ $detalle['nombre'] }}
                                            </td>
                                            @php
                                                $lastName = $detalle['nombre'];
                                                $index++;
                                            @endphp
                                        @endif

                                        <td class="align-middle text-fontsize-12">{{ $detalle['cargo'] }}</td>
                                        @foreach ($detalle['dias'] as $dia)
                                            <td class="align-middle text-fontsize-12">$ {{ number_format($dia, 2) }}</td>
                                        @endforeach
                                        <td class="align-middle text-fontsize-12">$
                                            {{ number_format($detalle['total_adicional'], 2) }}
                                        </td>
                                        <td class="align-middle text-fontsize-12">
                                            {{ implode(',', $detalle['detalle_adicional']) }}
                                        </td>
                                        @if ($isFirstRowForName)
                                            <td rowspan="{{ $rowspan }}" class="align-middle text-fontsize-12">$
                                                {{ number_format($totalDias, 2) }}</td>
                                        @endif
                                        <td class="align-middle text-fontsize-12">$
                                            {{ number_format($detalle['total_descuento'], 2) }}</td>
                                        <td class="align-middle text-fontsize-12">
                                            {{ implode(',', $detalle['detalle_descuento']) }}
                                        </td>
                                        <!-- pago prestamo -->
                                        <td class="align-middle text-fontsize-12">$
                                            {{ number_format(collect($detalle['prestamo'])->sum('pagos'), 2) }}</td>
                                        @if ($isFirstRowForName)
                                            <td rowspan="{{ $rowspan }}" class="align-middle text-fontsize-12">$
                                                {{ number_format($liquidoRecibirTotal, 2) }}</td>
                                        @endif
                                        <td class="align-middle text-fontsize-12">
                                            {{ implode(',', $detalle['observacion']) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Fila de totales generales -->
                                <tr>
                                    <td colspan="9"><strong>Total General:</strong></td>
                                    <td><strong style="font-size: 12px;">$
                                            {{ number_format($totalAdicionales, 2) }}</strong></td>
                                    <td></td> <!-- Detalle adicional -->
                                    <td><strong style="font-size: 12px;">$ {{ number_format($totalPagoDias, 2) }}</strong>
                                    </td>
                                    <td><strong style="font-size: 12px;">$
                                            {{ number_format($totalDescuentos, 2) }}</strong></td>
                                    <td></td> <!-- Detalle descuento -->
                                    <!-- Total descuentos prestamos -->
                                    <td>
                                        <strong style="font-size: 12px;">$
                                            {{ number_format($totalPagosPrestamos, 2) }}</strong>
                                    </td>
                                    <td><strong style="font-size: 12px;">$ {{ number_format($totalRecibir, 2) }}</strong>
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
