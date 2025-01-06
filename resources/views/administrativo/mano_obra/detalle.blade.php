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
                            <div class="col-md-10">
                                <h5>Semana: {{ $mano_obra->semana }}</h5>
                                <h6>{{ dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin) }}</h6>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_mano_obra">Guardar</button>
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
                        <table class="table table-bordered table-hover" id="tabla-planificacion">
                            <thead>
                                <tr>
                                    <th class="text-fontsize-12">Nro</th>
                                    <th class="text-fontsize-12">Apellidos y Nombres</th>
                                    <th class="text-fontsize-12">Cargo</th>
                                    <th class="text-fontsize-12 text-center">L</th>
                                    <th class="text-fontsize-12 text-center">M</th>
                                    <th class="text-fontsize-12 text-center">M</th>
                                    <th class="text-fontsize-12 text-center">J</th>
                                    <th class="text-fontsize-12 text-center">V</th>
                                    <th class="text-fontsize-12 text-center">S</th>
                                    <th class="text-fontsize-12">Adicionales</th>
                                    <th class="text-fontsize-12">Detalle Adicionales</th>
                                    <th class="text-fontsize-12">TOTAL</th>
                                    <th class="text-fontsize-12">Descuento</th>
                                    <th class="text-fontsize-12">Detalle Descuento</th>
                                    <th class="text-fontsize-12">Descuento Prestamo</th>
                                    <th class="text-fontsize-12">Liquido a Recibir</th>
                                    <th class="text-fontsize-12">Observaciones</th>
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
                                                    return $d['total'] - $d['total_descuento'];
                                                });
                                        }

                                        // Sumar los valores para los totales generales
                                        $totalAdicionales += $detalle['total_adicional'];
                                        $totalPagoDias += array_sum($detalle['dias']) + $detalle['total_adicional'];
                                        $totalDescuentos += $detalle['total_descuento'];
                                        $totalRecibir += $detalle['total'] - $detalle['total_descuento'];
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
                                        <td class="align-middle text-fontsize-12"></td>
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
                                    <td colspan="9"><strong>Total:</strong></td>
                                    <td><strong>$ {{ number_format($totalAdicionales, 2) }}</strong></td>
                                    <td></td> <!-- Detalle adicional -->
                                    <td><strong>$ {{ number_format($totalPagoDias, 2) }}</strong></td>
                                    <td><strong>$ {{ number_format($totalDescuentos, 2) }}</strong></td>
                                    <td></td> <!-- Detalle descuento -->
                                    <td></td> <!-- Firma -->
                                    <td><strong>$ {{ number_format($totalRecibir, 2) }}</strong></td>
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
