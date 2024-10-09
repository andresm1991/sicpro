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
        }

        p{
            padding: 0 !important;
            margin: 1px
        }
        .page-title {
            text-align: center;
            font-size: 16px; /* Tamaño del título */
            font-weight: bold; /* Grosor del título */
            margin: 20px 0; /* Margen superior e inferior */
        }
        .title{
            font-size: 14px; /* Tamaño del título */
            font-weight: bold; /* Grosor del título */
        }
        .text-content{
            font-size: 14px;
        }

        /* Estilos para la marca de agua */
        .watermark img {
            width: 80%;  /* Ajusta la imagen al 80% del ancho de la página */
            height: auto; /* Mantiene la relación de aspecto */
            position: absolute;
            top: 25%;  /* Ajustar el margen superior para centrar */
            left: 10%; /* Ajustar el margen izquierdo para centrar */
            opacity: 0.2; /* Transparencia */
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

        .td-rigth {
            padding: 5px;
            border: 1px solid #ddd;
            text-align: right !important;
            background-color: #ffffff;
        }

        .items th {
            background-color: #f2f2f2;
        }

        /* Definir ancho fijo para las nuevas columnas */
        th:nth-child(11), td:nth-child(11), /* Detalle Adicional */
        th:nth-child(14), td:nth-child(14), /* Detalle Descuento */
        th:nth-child(16), td:nth-child(16)   {
            width: 100px; /* Ajustar el ancho según sea necesario */
            word-wrap: break-word; /* Ajustar contenido largo */
        }

        th:nth-child(1), td:nth-child(1), /* Detalle Adicional */
        th:nth-child(12), td:nth-child(12)  /* Detalle Descuento */ {
            width: 20px; /* Ajustar el ancho según sea necesario */
        }

        /* Definir un ancho mínimo para otras columnas */
        th, td {
            min-width: 50px; /* Asegura que las columnas no se encojan demasiado */
        }

        /* Asegura que las columnas principales mantengan su tamaño adecuado */
       
        th:nth-child(2), td:nth-child(2), /* Nombres y Apellidos */
        th:nth-child(3), td:nth-child(3), /* Cargo */
        th:nth-child(10), td:nth-child(10), /* Adicionales */
        th:nth-child(12), td:nth-child(12), /* Descuentos */
        th:nth-child(13), td:nth-child(13), /* TOTAL */
        th:nth-child(15), td:nth-child(15), /* Liquido a Recibir */
        th:nth-child(16), td:nth-child(16),
        th:nth-child(17), td:nth-child(17)   {
            min-width: 80px;
        }
        /* end */
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #777;
        }
        
         /* Estilo para la clase .row (reseteamos el margen de las filas) */
    .row {
        width: 100%;
        clear: both;
        margin-right: -15px;
        margin-left: -15px;
    }

    /* Estilo para las columnas usando float */
    .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, 
    .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
        float: left;  /* Flotar las columnas a la izquierda */
        padding-right: 15px;
        padding-left: 15px;
    }

    /* Anchos específicos para cada tamaño de columna */
    .col-sm-1 { width: 8.333333%; }
    .col-sm-2 { width: 16.666667%; }
    .col-sm-3 { width: 25%; }
    .col-sm-4 { width: 33.333333%; }
    .col-sm-5 { width: 41.666667%; }
    .col-sm-6 { width: 50%; }
    .col-sm-7 { width: 58.333333%; }
    .col-sm-8 { width: 66.666667%; }
    .col-sm-9 { width: 75%; }
    .col-sm-10 { width: 83.333333%; }
    .col-sm-11 { width: 91.666667%; }
    .col-sm-12 { width: 100%; }

    /* Asegurarse de que el contenedor colapsa flotantes */
    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }
    </style>
</head>

<body>
    <!-- Marca de agua -->
    <div class="watermark">
        <img src="data:image/png;base64,{{ $logo_base64 }}"/>
    </div>

    <h1 class="page-title">ORDEN DE TRABAJO CONTRATISTA<br> Nro. {{ $info['orden'] }}</h1>

    <div class="row clearfix">
        <div class="col-sm-6">
            <p><strong class="title">Proyecto:</strong> <span class="text-content">{{ $info['proyecto'] }}</span></p>
            <p><strong class="title">Categoria:</strong> <span class="text-content">{{ $info['categoria'] }}</span></p>
            <p><strong class="title">Etapa:</strong> <span class="text-content">{{ $info['etapa'] }}</span></p>
            <p><strong class="title">Estado:</strong> <span class="text-content">{{ $info['estado'] }}</span></p>
        </div>
        <div class="col-sm-6">
            <p><strong class="title">Contratista:</strong> <span class="text-content">{{ $info['contratista'] }}</span></p>
            <p><strong class="title">Fecha:</strong> <span class="text-content">{{ $info['fecha'] }}</span></p>
            <p><strong class="title">Entrega:</strong> <span class="text-content"> {{ $info['plazo'] }}</span></p>
        </div>
    </div>
    

    <div class="content">
        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Unidad Medida</th>
                        <th>Valor Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($info['detalle'] as $index => $detalle)
                        <tr>
                            <td>{{ $index +1 }}</td>
                            <td>
                                {{ $detalle['producto'] }}
                            </td>
                            <td>
                                {{ $detalle['cantidad'] }}
                            </td>
                            <td>
                                {{ $detalle['unidad_medida'] }}
                            </td>
                            <td>
                                {{ $detalle['valor_unitario'] }}
                            </td>
                            <td>
                                {{ $detalle['total'] }}
                            </td>
                        </tr>
                    @endforeach
                    <!-- Fila de totales generales -->
                    
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="td-rigth">
                            <strong>Total:</strong><br>
                            <strong>Pagos:</strong><br>
                            <strong>Saldo:</strong>
                        </td>
                        <td >
                            <strong>$ {{ $info['valor_contratado'] }}</strong><br>
                            <strong>$ {{ $info['avance'] }}</strong><br>
                            <strong>$ {{ $info['saldo'] }}</strong>
                        </td>
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
