<!-- resources/views/pdf/orden_pedido.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte Mano Obra</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            text-transform: uppercase;
        }

        .page-title {
            text-align: center;
            font-size: 24px;
            /* Tamaño del título */
            font-weight: bold;
            /* Grosor del título */
            margin: 20px 0;
            /* Margen superior e inferior */
        }

        .title {
            font-size: 16px;
            /* Tamaño del título */
            font-weight: bold;
            /* Grosor del título */
        }

        .text-content {
            font-size: 16px;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-titulo {
            font-size: 16px;
            /* Tamaño del título */
            font-weight: bold;
            /* Grosor del título */
        }

        .text-subtitulo {
            font-size: 16px;
        }

        /* Estilos para la marca de agua */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.2;
            /* Transparencia */

            background-position: center;
            background-size: contain;
            background-repeat: no-repeat;
        }

        .header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            /* Centrar verticalmente el contenido */
            position: relative;
        }

        .header img {
            max-width: 150px;
            height: auto;
        }

        .content {
            margin: 0 auto;
            width: 100%;
        }

        .details {
            margin-bottom: 20px;
        }

        .details .left,
        .details .right {
            width: 50%;
            float: left;
        }

        .details .right {
            /* text-align: right;*/
        }

        .clear {
            clear: both;
        }

        /* CSS Table */
        .items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .items th,
        .items td {
            padding: 5px;
            border: 1px solid #ddd;
            text-align: center;
            background-color: #ffffff;
        }

        .items th {
            background-color: #f2f2f2;
        }

        /* Definir ancho fijo para las nuevas columnas */
        th:nth-child(11),
        td:nth-child(11),
        /* Detalle Adicional */
        th:nth-child(14),
        td:nth-child(14),
        /* Detalle Descuento */
        th:nth-child(16),
        td:nth-child(16) {
            width: 100px;
            /* Ajustar el ancho según sea necesario */
            word-wrap: break-word;
            /* Ajustar contenido largo */
        }

        th:nth-child(1),
        td:nth-child(1),
        /* Detalle Adicional */
        th:nth-child(12),
        td:nth-child(12)

        /* Detalle Descuento */
            {
            width: 20px;
            /* Ajustar el ancho según sea necesario */
        }

        /* Definir un ancho mínimo para otras columnas */
        th,
        td {
            min-width: 50px;
            /* Asegura que las columnas no se encojan demasiado */
        }

        /* Asegura que las columnas principales mantengan su tamaño adecuado */

        th:nth-child(2),
        td:nth-child(2),
        /* Nombres y Apellidos */
        th:nth-child(3),
        td:nth-child(3),
        /* Cargo */
        th:nth-child(10),
        td:nth-child(10),
        /* Adicionales */
        th:nth-child(12),
        td:nth-child(12),
        /* Descuentos */
        th:nth-child(13),
        td:nth-child(13),
        /* TOTAL */
        th:nth-child(15),
        td:nth-child(15),
        /* Liquido a Recibir */
        th:nth-child(16),
        td:nth-child(16),
        th:nth-child(17),
        td:nth-child(17) {
            min-width: 80px;
        }

        /* end */
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>
    <!-- Marca de agua -->
    <div class="watermark">
        <img src="{{ logoBase64() }}" />
    </div>

    <div class="content">

        <div class="header">
            <h1 class="text-center">Mano de obra</h1>
            <p>
                <span class="text-titulo">Proyecto: </span> <span
                    class="text-subtitulo">{{ isset($mano_obra->proyecto) ? $mano_obra->proyecto->nombre_proyecto : 'General' }}</span>
            </p>
            <p>
                <span class="text-titulo">Fecha:</span> <span
                    class="text-subtitulo">{{ dateFormatHumansManoObra($mano_obra->fecha_desde_formatted, $mano_obra->fecha_hasta_formatted) }}</span>
            </p>

        </div>

        <div class="items">
            <table id="tabla-planificacion">
                <thead>
                    <tr>
                        <th scope="col">Personal</th>
                        <th scope="col">sueldo</th>
                        <th scope="col">horas extras</th>
                        <th scope="col">total ganado</th>
                        <th scope="col">fondos</th>
                        <th scope="col">decimo tercer sueldo</th>
                        <th scope="col">decimo cuarto sueldo</th>
                        <th scope="col">total ingresos</th>
                        <th scope="col">i.e.s.s</th>
                        <th scope="col">atrasos y faltas</th>
                        <th scope="col">anticipos</th>
                        <th scope="col">Prestamo iess</th>
                        <th scope="col">quincena</th>
                        <th scope="col">prestamo jp</th>
                        <th scope="col">total descuentos</th>
                        <th scope="col">total a recibir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mano_obra->detalles as $personal)
                        <tr class="elementos-agregados">
                            <td>
                                {{ $personal->proveedor->nombres }} {{ $personal->proveedor->apellidos }}
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->sueldo_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->horas_extras_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>{{ $personal->total_ganado_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->fondo_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->decimo_tercero_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->decimo_cuarto_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->total_ingresos_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->iess_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->atrasos_faltas_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->anticipos_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->prestamo_iess_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->quincena_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->prestamo_jp_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->total_descuentos_formatted }}</span>
                            </td>
                            <td class="edit-item" style="cursor: pointer;">
                                <span>$ {{ $personal->total_recibir_formatted }}</span>
                            </td>
                        </tr>
                    @endforeach

                    <tr id="tr-default" style="display: {{ $mano_obra->detalles->isEmpty() ? '' : 'none' }}">
                        <td colspan="16" class="text-center">No existen elementos en la lista...</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right; font-weight: bold">totales</td>
                        <td>$ {{ $mano_obra->total_sueldos_formatted }}</td>
                        <td>$ {{ $mano_obra->total_horas_hextras_formatted }}</td>
                        <td>$ {{ $mano_obra->total_ganado_formatted }}</td>
                        <td>$ {{ $mano_obra->total_fondos_formatted }}</td>
                        <td>$ {{ $mano_obra->total_decimo_tercero_formatted }}</td>
                        <td>$ {{ $mano_obra->total_decimo_cuarto_formatted }}</td>
                        <td>$ {{ $mano_obra->total_ingreso_formatted }}</td>
                        <td>$ {{ $mano_obra->total_iess_formatted }}</td>
                        <td>$ {{ $mano_obra->total_atrasos_faltas_formatted }}</td>
                        <td>$ {{ $mano_obra->total_anticipos_formatted }}</td>
                        <td>$ {{ $mano_obra->total_prestamo_iess_formatted }}</td>
                        <td>$ {{ $mano_obra->total_quincena_formatted }}</td>
                        <td>$ {{ $mano_obra->total_prestamos_jp_formatted }}</td>
                        <td>$ {{ $mano_obra->total_descuentos_formatted }}</td>
                        <td>$ {{ $mano_obra->total_recibir_formatted }}</td>
                    </tr>

                </tfoot>
            </table>
        </div>
    </div>

    <div class="footer">
        Esta es un informe generado electrónicamente. No requiere firma.
    </div>
</body>

</html>
