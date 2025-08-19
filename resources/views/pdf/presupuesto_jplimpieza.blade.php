<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            text-transform: uppercase;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }



        .table-header {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-header th,
        .table-header td {
            border: 0;
            background-color: transparent;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        ul {

            padding: 0 10px 0 10px;
        }

        #table-detalle {
            width: 100%;
            border-collapse: collapse;
        }

        #table-detalle,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #b6bcdf;
        }

        #table-detalle {
            border-collapse: collapse;
            width: 100%;
        }

        #table-detalle th,
        #table-detalle td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid black;
        }


        .footer {
            margin-top: 25px;
            font-size: 10px;
            color: #131212;
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
            /* Usar la imagen como fondo */
            background-position: center;
            background-size: cover;
            /* Ajustar la imagen para cubrir todo el fondo */
            background-repeat: no-repeat;
        }

        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Ajustar la imagen sin distorsión */
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
            opacity: 0.2;
        }

        .bg-success {
            background-color: #28a745 !important;
        }
    </style>
</head>

<body>

    <div class="watermark">
        <img src="{{ logoBase64() }}" />
    </div>

    <div class="container">
        <table class="table-header">
            <tbody>
                <tr>
                    <td style="width: 1px;">
                        <img src="{{ logoBase64() }}" alt="Logo del Proyecto" width="150">
                    </td>
                    <td class="text-center">
                        <h2>presupuesto</h2>
                    </td>
                </tr>
            </tbody>
        </table>

        <p>
            <strong>nombre proyecto:</strong> {{ $proyecto->nombre_proyecto }}
        </p>
        <p>
            <strong>entidad:</strong> {{ $proyecto->entidad }}
        </p>
        <p>
            <strong>metraje contratado:</strong> {{ $proyecto->metros_contratado }}
        </p>
        <p>
            <strong>PRECIO X METRO:</strong> $ {{ $proyecto->precio_por_metro }}
        </p>
        <p>
            <strong>VALOR DEL CONTRATO MENSUAL:</strong> $ {{ $proyecto->valor_contratado_mensual_formatted }}
        </p>
        <p>
            <strong>TIEMPO CONTRATADO:</strong> {{ $proyecto->tiempo_contratado }}
        </p>
        <p>
            <strong>VALOR DEL CONTRATO TOTAL:</strong> $ {{ $proyecto->total_contratado_formatted }} sin iva
        </p>

        <table class="table" id="table-detalle">
            <thead>
                <tr>
                    <th scope="col">Nro.</th>
                    <th scope="col">Rubro</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Valor Unit</th>
                    <th scope="col">Sub. Total</th>
                    <th scope="col">meses</th>
                    <th scope="col">total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $index = 1;
                @endphp
                @forelse ($categorias as $categoria)
                    <tr style="background-color: #b6bcdf;">
                        <td colspan="7" class="align-middle">
                            <strong>{{ $categoria->descripcion . ' ' . $categoria->detalle }}</strong>
                        </td>
                    </tr>
                    @forelse ($categoria->hijos as $hijo)
                        <tr>
                            <td class="align-middle">{{ $index }}</td>
                            <td class="align-middle" style="cursor: pointer;">
                                <span>{{ $hijo->descripcion }}</span>
                            </td>
                            <td class="align-middle">
                                {{ $hijo->presupuestoProyecto->cantidad ?? 0 }}

                            </td>
                            <td class="align-middle">
                                $ {{ number_format($hijo->presupuestoProyecto->precio_unitario ?? 0, 4) }}
                            </td>
                            <td class="align-middle">
                                $
                                {{ number_format(
                                    ($hijo->presupuestoProyecto->cantidad ?? 1) * ($hijo->presupuestoProyecto->precio_unitario ?? 0),
                                    4,
                                ) }}
                            </td>
                            <td class="align-middle">
                                {{ $hijo->presupuestoProyecto->meses ?? 0 }}
                            </td>
                            <td class="align-middle">
                                ${{ number_format($hijo->total_presupuesto, 4) }}
                            </td>
                        </tr>
                        @php
                            $index += 1;
                        @endphp
                    @empty
                    @endforelse
                    <tr style="background-color: #d7ecdc;" class="fila-total">
                        <td colspan="6" class="font-weight-bold">
                            <strong>Total General</strong>
                        </td>
                        <td colspan="1" class="font-weight-bold">
                            ${{ number_format($categoria->hijos->sum('total_presupuesto'), 4) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            No se encontraron datos para mostrar....
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold">total contratado</td>
                    <td colspan="1">$ {{ $proyecto->total_contratado_formatted }}</td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold">total estimado</td>
                    <td colspan="1">$ {{ number_format($categorias->sum('total_categoria'), 4) }}</td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold">utilidad estimada</td>
                    <td colspan="1"> $
                        {{ number_format($proyecto->total_contratado - $categorias->sum('total_categoria'), 4) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>

</html>
