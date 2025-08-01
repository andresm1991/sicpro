<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Proforma de Trabajo</title>
    <style>
        /* Mantenemos solo los estilos más básicos y universales aquí */
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            /* DejaVu Sans es bueno para UTF-8 en DomPDF */
            color: #333;
            font-size: 12px;
        }

        @page {
            margin: 25px;
        }

        @page portrait {
            size: a4 portrait;
        }

        @page landscape {
            size: a4 landscape;
        }

        /* Contenedor que usará la página vertical */
        .page-portrait {
            page: portrait;
        }

        /* Contenedor que usará la página horizontal */
        .page-landscape {
            page: landscape;
        }

        /* Este estilo es para asegurar el salto de página, independientemente de la orientación */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <div class="page-portrait">
        <div class="proforma-content">
            <div style="max-width: 100%; margin: auto; padding: 15px;">

                <!-- Cabecera: Usando una tabla para la maquetación -->
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <!-- Usa tu helper de Base64 o una ruta absoluta con public_path() -->
                            <img src="{{ logoBase64() }}" style="width: 170px; height: auto;" alt="Logo">
                        </td>
                        <td style="width: 50%; text-align: right; vertical-align: top;">
                            <h1 style="margin: 0; font-size: 18px; color: #000;">{{ $empresa[0]->detalle }}</h1>
                            <p style="margin: 5px 0; font-size: 14px;"><strong>RUC: {{ $empresa[4]->detalle }}</strong>
                            </p>
                            <h2
                                style="margin: 15px 0 0; font-size: 16px; color: #000; border-bottom: 2px solid #000; display: inline-block; padding-bottom: 2px;">
                                PROFORMA DE TRABAJO</h2>
                        </td>
                    </tr>
                </table>

                <!-- Información del Cliente -->
                <table
                    style="width: 100%; border: 1px solid #000; margin-top: 20px; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 3px 8px;">
                            <strong>Prof. No:</strong>
                            {{ $proforma->numero }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 8px;">
                            <strong>Nombre:</strong> {{ strtoupper($proforma->cliente->nombre) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 8px;">
                            <strong>Dirección:</strong> {{ strtoupper($proforma->cliente->direccion ?? '-') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 8px;">
                            <strong>Teléfono:</strong> {{ strtoupper($proforma->cliente->telefono ?? '-') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 8px;">
                            <strong>Fecha:</strong> {{ $proforma->fecha_formatted }}
                        </td>
                    </tr>

                    @if ($tipo == 'diseno_planos')
                        <tr>
                            <td style="padding: 3px 8px;">
                                <strong>Ubicación del lote:</strong> {{ strtoupper($proforma->ubicacion_lote ?? '-') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 8px;">
                                <strong>Área de lote:</strong> {{ $proforma->area_lote ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 8px;">
                                <strong>Presupuesto aproximado para vivienda:</strong>
                                $ {{ number_format($proforma->presupuesto ?? 0, 2) }}
                            </td>
                        </tr>
                    @endif
                </table>

                <!-- Tabla Principal de Items -->
                <table
                    style="width: 100%; border-collapse: collapse; border-left: 1px solid #000; border-right: 1px solid #000; font-size: 13px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="width: 5%; border: 1px solid #000; padding: 8px; text-align: center;">N°</th>
                            <th style="width: 55%; border: 1px solid #000; padding: 8px; text-align: center;">
                                DESCRIPCIÓN</th>
                            <th style="width: 10%; border: 1px solid #000; padding: 8px; text-align: center;">
                                {{ $tipo == 'adecentamientos' ? 'CANT' : 'AREA M2' }} </th>
                            <th style="width: 15%; border: 1px solid #000; padding: 8px; text-align: center;">UNITARIO
                            </th>
                            <th style="width: 15%; border: 1px solid #000; padding: 8px; text-align: center;">V/ TOTAL
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila de ejemplo -->
                        @forelse ($tipo == 'adecentamientos' ? $proforma->detalleAdecentamientos : $proforma->detallePlanos as $index => $detalle)
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                                    {{ $index + 1 }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">
                                    {{ strtoupper($detalle->producto->nombre) }}
                                </td>
                                <td style="border: 1px solid #000; padding: 8px;">
                                    {{ $tipo == 'adecentamientos' ? $detalle->cantidad : $detalle->area }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">
                                    ${{ $detalle->precio_unitario_format }}</td>
                                <td style="border: 1px solid #000; padding: 8px; text-align: right;">
                                    ${{ $detalle->total_format }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; text-align: center;">1</td>
                                <td style="border: 1px solid #000; padding: 8px;"></td>
                                <td style="border: 1px solid #000; padding: 8px;"></td>
                                <td style="border: 1px solid #000; padding: 8px;"></td>
                                <td style="border: 1px solid #000; padding: 8px; text-align: right;">0,00</td>
                            </tr>
                        @endforelse

                        <!-- Filas en blanco para rellenar espacio -->
                        @for ($i = 0; $i < 2; $i++)
                            <tr>
                                <td style="border: 1px solid #000; padding: 15px;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <!-- Sección inferior con Notas y Totales (usando una tabla) -->
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- Columna de Notas -->

                        <!-- Columna de Totales -->
                        <td
                            style=" vertical-align: top; border-right: 1px solid #000; border-bottom: 1px solid #000; border-left: 1px solid #000;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tr>
                                    <td style="text-align: right; padding: 8px; width: 70%;">SUBTOTAL</td>
                                    <td
                                        style="text-align: right; padding: 8px; border-left: 1px solid #000; border-bottom: 1px solid #000; width: 30%;">
                                        $ {{ $proforma->subtotal_formatted }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: right; padding: 8px;">DESCUENTO% {{ $proforma->descuento }}
                                    </td>
                                    <td
                                        style="text-align: right; padding: 8px; border-left: 1px solid #000; border-bottom: 1px solid #000;">
                                        {{ $proforma->total_descuento_formatted }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: right; padding: 8px;">IVA% {{ $proforma->iva }}</td>
                                    <td
                                        style="text-align: right; padding: 8px; border-left: 1px solid #000; border-bottom: 1px solid #000;">
                                        {{ $proforma->total_iva_formatted }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: right; padding: 8px; font-weight: bold;">TOTAL</td>
                                    <td
                                        style="text-align: right; padding: 8px; border-left: 1px solid #000; font-weight: bold;">
                                        {{ $proforma->total_formatted }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>


                @if ($tipo == 'diseno_planos')
                    <p><strong>INCLUYE:</strong></p>
                    @foreach ($proforma->incluye as $incluye)
                        <p style="margin: 5px 0; font-size: 13px;">- {{ $incluye }}</p>
                    @endforeach
                    <!-- Información Adicional -->
                    <div style="margin-top: 20px; font-size: 13px;">
                        <p><strong>PLAZO DE EJECUCION DEL PROYECTO:</strong></p>
                        <p>{!! nl2br(e($proforma->plazo_ejecucion)) !!}</p>
                    </div>

                    <div style="margin-top: 20px; font-size: 13px;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align: left;">FORMA DE PAGO</th>
                                    <th style="text-align: left;">ABONO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: left; padding: 5px; width: 500px;">
                                        @foreach ($proforma->forma_pago as $forma_pago)
                                            <p>{{ $forma_pago }}</p>
                                        @endforeach
                                    </td>
                                    <td style="text-align: left; padding: 5px; width: 1px;">
                                        @foreach ($proforma->abono as $abono)
                                            <p>{{ $abono ?? '0' }}%</p>
                                        @endforeach
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <strong>Notas:</strong>
                    <p style="margin-top: 5px;">{{ $proforma->notas }}</p>
                @endif

                <!-- Firma de Autorización -->
                <div style="margin-top: 50px; text-align: center;">
                    <div style="border-top: 1px solid #000; width: 300px; margin: 0 auto 5px;"></div>
                    <p style="font-size: 14px; margin: 0;">FIRMA DE AUTORIZACIÓN</p>
                </div>

                @if ($tipo == 'adecentamientos')
                    <!-- Términos y Condiciones -->
                    <div style="background-color: #f2f2f2; padding: 15px; margin-top: 30px; border: 1px solid #ddd;">
                        <h3 style="text-align: center; margin: 0 0 15px 0; font-size: 16px;">TERMINOS Y CONDICIONES</h3>
                        <p style="margin: 5px 0; font-size: 13px;"><strong>Validez de la proforma:</strong>
                            {{ $proforma->validez }}</p>
                        <p style="margin: 5px 0; font-size: 13px;"><strong>Forma de pago:</strong>
                            {{ $proforma->forma_pago }}
                        </p>
                        <p style="margin: 5px 0; font-size: 13px;"><strong>Tiempo de entrega:</strong>
                            {{ $proforma->plazo_entrega }}</p>
                        <p style="margin: 5px 0; font-size: 13px;"><strong>Email:</strong> {{ $empresa[3]->detalle }}
                        </p>
                        <p style="margin: 5px 0; font-size: 13px;"><strong>Telf.:</strong> {{ $empresa[2]->detalle }}
                        </p>
                        <p style="margin: 5px 0; font-size: 13px;"><strong>Dir.:</strong> {{ $empresa[1]->detalle }}
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>



    {{-- Comprobamos si la variable $programaData fue pasada y no es nula --}}
    @if (isset($programaData) && $programaData)
        {{-- Esto fuerza a que el programa arquitectónico empiece en una nueva página --}}
        <div class="page-break page-landscape">
            {{-- Incluimos la vista del programa, pasándole los datos necesarios --}}
            @include('pdf.programa_arquitectonico', $programaData)
        </div>
    @endif
</body>

</html>
