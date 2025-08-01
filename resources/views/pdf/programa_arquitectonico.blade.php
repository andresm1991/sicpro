<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Programa Arquitectónico</title>
    <style>
        /* Estilos optimizados para dompdf */
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            /* DejaVu Sans para mejor soporte de caracteres */
            color: #333;
            font-size: 10px;
            /* Un tamaño más pequeño suele funcionar mejor en PDFs */
        }

        .main-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            font-style: italic;
        }

        .style-bar {
            background-color: #fbe5d6;
            padding: 6px;
            font-weight: bold;
            margin-bottom: 15px;
            border: 1px solid #e6e6e6;
            width: 100%;
        }

        .style-bar .estilo {
            float: left;
        }

        .style-bar .contemporanea {
            float: right;
        }

        table.program-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: avoid;
            /* Intenta no cortar la tabla a la mitad de una página */
        }

        .program-table th,
        .program-table td {
            border: 1px solid #a6a6a6;
            padding: 4px 6px;
            text-align: left;
            word-wrap: break-word;
            /* Para que el texto largo no rompa la tabla */
        }

        .category-header th {
            background-color: #5b9bd5;
            color: white;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            padding: 8px;
        }

        .columns-header th {
            background-color: #8faadc;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .program-table tbody tr {
            background-color: #ffffff;
        }

        .program-table tbody tr:nth-child(even) {
            background-color: #ddebf7;
        }

        .subtotal-row td {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: right;
        }

        .subtotal-row .label {
            text-align: right;
            padding-right: 15px;
        }

        .totals-section {
            margin-top: 30px;
            font-size: 12px;
            font-weight: bold;
            page-break-inside: avoid;
        }

        .totals-section table {
            float: right;
            width: 350px;
        }

        .totals-section td {
            padding: 5px;
        }

        .totals-section .label {
            text-align: right;
        }

        .totals-section .value {
            text-align: left;
            padding-left: 20px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>

    <h1 class="main-title">PROGRAMA ARQUITECTONICO</h1>

    <div class="style-bar clearfix">
        <div class="estilo">ESTILO</div>
        <div class="contemporanea">{{ $programa->estilo ?? 'CONTEMPORANEA' }}</div>
    </div>
    <div class="style-bar clearfix">
        <div class="estilo">LINKS REF.</div>
        <div class="contemporanea">{!! nl2br(e($programa->urls_referencias ?? 'N/A')) !!}</div>
    </div>

    @foreach ($groupedEspacios as $categoriaNombre => $espacios)
        <table class="program-table">
            <thead>
                <tr class="category-header">
                    <th colspan="8">{{ $categoriaNombre }}</th>
                </tr>
                <tr class="columns-header">
                    <th style="width: 15%;">ESPACIO</th>
                    <th style="width: 7%;">CANTIDAD</th>
                    <th style="width: 15%;">ACTIVIDADES</th>
                    <th style="width: 15%;">MOBILIARIO</th>
                    <th style="width: 8%;">USUARIO</th>
                    <th style="width: 8%;">M2</th>
                    <th style="width: 17%;">OBSERVACIONES</th>
                    <th style="width: 15%;">LINK REF</th>
                </tr>
            </thead>
            <tbody>
                @php $rowCount = 0; @endphp
                @forelse($espacios as $espacio)
                    @if ($espacio->espacio || $espacio->cantidad > 1 || $espacio->m2 > 0)
                        {{-- Solo mostrar filas con datos --}}
                        <tr>
                            <td>{{ $espacio->espacio }}</td>
                            <td style="text-align: center;">{{ $espacio->cantidad }}</td>
                            <td>{!! nl2br(e($espacio->actividades)) !!}</td>
                            <td>{!! nl2br(e($espacio->mobiliario)) !!}</td>
                            <td style="text-align: center;">{{ $espacio->usuario }}</td>
                            <td style="text-align: right;">{{ number_format($espacio->m2, 2) }}</td>
                            <td>{!! nl2br(e($espacio->observaciones)) !!}</td>
                            <td>{!! nl2br(e($espacio->link_ref)) !!}</td>
                        </tr>
                        @php $rowCount++; @endphp
                    @endif
                @empty
                    <!-- No se hace nada si no hay espacios, no se muestra la tabla vacía -->
                @endforelse

                {{-- Rellenar con filas vacías para que siempre haya un mínimo de 4 (o las que tengan datos) --}}
                @for ($i = $rowCount; $i < 4; $i++)
                    <tr>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr class="subtotal-row">
                    <td class="label" colspan="5">SUBTOTAL M2</td>
                    <td style="text-align: right;">{{ number_format($espacios->sum('m2'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    @endforeach

    <div class="totals-section">
        <table>
            <tr>
                <td class="label">TOTAL M2 AREA INTERIORES</td>
                <td class="value">{{ number_format($total_m2_interiores, 2) }}</td>
            </tr>
            <tr>
                <td class="label">TOTAL M2 AREA EXTERIORES</td>
                <td class="value">{{ number_format($total_m2_exteriores, 2) }}</td>
            </tr>
        </table>
    </div>

</body>

</html>
