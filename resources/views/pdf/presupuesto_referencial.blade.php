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

        .header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            position: relative;
        }

        .header img {
            max-width: 200px;
            height: auto;
        }


        .header .content {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            position: absolute;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
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

        .table {
            border-collapse: collapse;
            width: 100%;
        }

        .table th,
        .table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid black;
        }


        .footer {
            margin-top: 25px;
            font-size: 10px;
            color: #131212;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="{{ logoBase64() }}" alt="Logo">
        <div class="content" style="text-align: center; width: 100%;  transform: translateX(-25%) translateY(25%) ;">
            <small class="text-center">PRESUPUESTO REFERENCIAL</small><br>
            <small>{{ $proyecto->nombre_proyecto }}</small>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Nro.</th>
                <th scope="col">Rubro</th>
                <th scope="col">UM</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Costo</th>
                <th scope="col">Sub. Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categorias as $categoria)
                @php
                    $total_categoria = 0;
                @endphp
                <tr id="{{ $categoria->id }}" style="background-color: #b6bcdf;">
                    <td colspan="6" class="align-middle">
                        {{ $categoria->nombre }}
                    </td>
                </tr>
                @forelse ($categoria->rubrosPresupuesto as $index => $rubro)
                    @php
                        $total_categoria += $rubro->presupuestoProyectos->sum(function ($item) {
                            return $item->cantidad * $item->valor_unitario;
                        });
                    @endphp
                    <tr>
                        <td class="align-middle">{{ $index + 1 }}</td>
                        <td class="align-middle"> {{ $rubro->nombre }}</td>
                        <td class="align-middle">{{ $rubro->unidad_medida->descripcion }}</td>
                        @foreach ($rubro->presupuestoProyectos as $presupuestoProyecto)
                            <td class="align-middle">{{ $presupuestoProyecto->cantidad }}</td>
                            <td class="align-middle">$ {{ $presupuestoProyecto->valor_unitario }}</td>
                            <td class="align-middle">
                                $
                                {{ number_format($presupuestoProyecto->cantidad * $presupuestoProyecto->valor_unitario, 2) }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                @endforelse
                <tr style="background-color: #d7ecdc;">
                    <td colspan="5">
                        Total General
                    </td>
                    <td colspan="1">
                        $ {{ number_format($total_categoria, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        No se encontraron datos para mostrar....
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
                $costos_directos = $categorias->sum(function ($item) {
                    return $item->rubrosPresupuesto->sum(function ($item) {
                        return $item->presupuestoProyectos->sum(function ($item) {
                            return $item->cantidad * $item->valor_unitario;
                        });
                    });
                });

                $costos_indirectos = ($costos_directos * $proyecto->costo_indirecto) / 100;
                $total_adquisiciones = $proyecto->adquisiciones->where('estado', 'Completado')->sum(function ($item) {
                    return $item->adquisiciones_detalle->sum(function ($item) {
                        $iva = $item->producto->iva ? $item->producto->iva : 0;
                        return calcularTotalProducto($item->cantidad_solicitada, $item->valor, $iva);
                    });
                });

                $saldo = $costos_directos - $total_adquisiciones;
            @endphp

            <tr>
                <td colspan="5">
                    <h4>COSTOS DIRECTOS</h4>
                </td>
                <td colspan="1">
                    $
                    {{ number_format($costos_directos, 2) }}
                </td>
            </tr>
            <tr style="background-color: #b6e5c1;">
                <td colspan="5" class="font-weight-bold editar-costo-indirecto" style="cursor: pointer;"
                    data-id="{{ $proyecto->id }}" data-porcentaje="{{ $proyecto->costo_indirecto }}">
                    <h4>COSTOS INDIRECTOS {{ $proyecto->costo_indirecto }}% </h4>
                </td>
                <td colspan="1">
                    $
                    {{ number_format($costos_indirectos, 2) }}
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <h4>TOTAL</h4>
                </td>
                <td colspan="1">
                    $
                    {{ number_format($costos_directos + $costos_indirectos, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
    <div class="footer">
        <p>
            <b>NOTA:</b> EL VALOR TOTAL NO INCLUYE IVA. ESTE PRESUPUESTO TIENE VIGENCIA 15 DIAS CALENDARIO
        </p>
    </div>

</body>

</html>
