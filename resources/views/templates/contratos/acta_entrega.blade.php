<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Acta de Entrega-Recepción - Proceso #{{ $proceso->id }}</title>
</head>

<body
    style="font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.6; color: #333; background-color: #ffffff; margin: 0; padding: 0;">
    <div
        style="max-width: 800px; margin: 20px auto; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.05); padding: 40px;">
        <!-- ======================= ENCABEZADO CON TABLA (MANTENIDO) ======================= -->
        <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #eeeeee; padding-bottom: 20px;">
            <tbody>
                <tr>
                    <td style="width: 50%; vertical-align: middle;">
                        {{-- Asegúrate que esta imagen exista en public/images/ --}}
                        <img src="{{ asset('images/prime_doc.png') }}" alt="Logotipo"
                            style="max-height: 70px; width: auto;">
                    </td>
                    <td
                        style="width: 50%; vertical-align: middle; text-align: right; font-family: Arial, sans-serif; font-size: 10pt; color: #666;">
                        <p style="margin: 0; line-height: 1.4;"><strong style="font-weight: bold;">CONSTRUCCIONES PRIME
                                JP S.A.</strong></p>
                        <p style="margin: 0; line-height: 1.4;">Av. 6 de noviembre y Augusto Gachet, sector El Colono
                        </p>
                        <p style="margin: 0; line-height: 1.4;">Santo Domingo, Ecuador</p>
                        <p style="margin: 0; line-height: 1.4;">Telf.: 0997457053</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ======================= CUERPO DEL ACTA (REEMPLAZADO) ======================= -->
        <div style="padding-top: 20px;">
            <h1 style="text-align: center; font-size: 18pt; margin-bottom: 30px; text-transform: uppercase;">Acta de
                Entrega-Recepción de Vivienda</h1>

            <div style="margin-bottom: 20px;">
                <p style="margin: 0;"><strong>Cliente:</strong> {{ $cliente->nombre }}</p>
                <p style="margin: 0;"><strong>Manzana:</strong> SN</p>
                <p style="margin: 0;"><strong>Unidad:</strong> {{ $proceso->unidad }}</p>
            </div>

            <p style="text-align: justify; margin-bottom: 15px;">
                En la ciudad de Santo Domingo, al {{ $fecha_entrega->day }} día del mes de
                {{ $fecha_entrega->translatedFormat('F') }} del año {{ $fecha_entrega->year }}, se suscribe la presente
                ACTA DE ENTREGA-RECEPCIÓN de la vivienda número {{ $proceso->unidad }}, manzana
                [ingresar información], del {{ $proceso->proyecto->nombre_proyecto }} ubicado en la vía
                {{ $proceso->proyecto->direccion }}, en la
                ciudad de [ingresar información], al tenor de las siguientes cláusulas:
            </p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                PRIMERA: COMPARECIENTES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Comparecen a la celebración de la presente ACTA DE
                ENTREGA RECEPCIÓN DE VIVIENDA por una parte CONSTRUCCIONES PRIME JP S.A, como a quien en adelante y para
                efectos de la presente Acta se le denominará como el VENDEDOR, y por otra parte el señor
                {{ $cliente->nombre_completo }} con CI: {{ $cliente->cedula }} a quién para efectos del presente
                instrumento se le denominara COMPRADOR. Los comparecientes son mayores de edad, hábiles para contratar y
                obligarse.</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                SEGUNDA: ANTECEDENTES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Con fecha
                {{ $fecha_reserva->translatedFormat('j \d\e F \d\e\l Y') }} se realizó la reserva de COMPRAVENTA de la
                vivienda número {{ $proceso->unidad }} manzana [ingresar información]. Los precios y formas de pago
                fueron determinados por mutuo acuerdo.</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                TERCERA: DECLARACIONES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Mediante este documento, el COMPRADOR acusa recibo de
                la entrega física del inmueble por parte del VENDEDOR. A partir de la fecha de suscripción de esta Acta,
                la Constructora se deslinda de toda responsabilidad por daños producidos por el uso de la casa posterior
                a esta fecha. Ambas partes declaran que, al momento de esta entrega, el inmueble se encuentra en
                perfecto estado de conservación y funcionamiento, incluyendo:</p>

            <ol style="padding-left: 40px;">
                <li style="margin-bottom: 10px;"> Infraestructura: Paredes, techos y pisos sin grietas, humedad ni
                    deterioro visible. Pintura en buen estado, reciente o sin manchas evidentes. Estructura sólida y sin
                    filtraciones.</li>
                <li style="margin-bottom: 10px;"> Puertas y Ventanas: Todas las puertas y ventanas funcionan
                    correctamente. Cerraduras y bisagras en buen estado. Vidrios completos, sin fisuras ni daños.</li>
                <li style="margin-bottom: 10px;"> Instalaciones: Eléctrica en funcionamiento, sin cables expuestos ni
                    cortocircuitos. Sanitaria con desagües y grifería sin fugas ni obstrucciones. Iluminación con todos
                    los interruptores y luminarias en funcionamiento.</li>
                <li style="margin-bottom: 10px;"> Mobiliario: Muebles empotrados o accesorios instalados correctamente
                    sin defectos.</li>
            </ol>

            {{-- Aquí van el resto de cláusulas del acta (CUARTA a SEPTIMA) --}}
            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                CUARTA: DAÑOS</h3>
            <p style="text-align: justify; margin-bottom: 15px;">La constructora se hace responsable por los daños
                ocurridos por defecto de construcción de acuerdo a lo que manda la ley. Los daños que resulten del uso y
                desgaste natural de los bienes no serán responsabilidad de la constructora.</p>
            <p style="text-align: justify; margin-bottom: 15px;">Se recomienda al COMPRADOR, que para realizar cualquier
                modificación ya sea de tipo estructural, mampostería, eléctrico, hidrosanitario, etc. se solicite el
                servicio o la consulta técnica correspondiente al técnico profesional de la constructora...</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                QUINTA: GARANTIAS</h3>
            <p style="text-align: justify; margin-bottom: 15px;">En Ecuador, se rigen principalmente por las
                disposiciones de la Ley de Defensa del Consumidor, el Código Civil y normas técnicas específicas de
                construcción...</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                RECOMENDACIONES PARA MANTENIMIENTO DEL BIEN INMBUEBLE</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Tomando en cuenta que la edificación de la vivienda es
                una construcción nueva que por ende pasará por el efecto de ASENTAMIENTO CONSTRUCTIVO se recomienda...
            </p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                SEXTA: CIRCUNSTANCIAS ADICIONALES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">El pago de las alícuotas se realizará directamente a la
                administración del CLUB PRIVADO CIUDAD CELESTE...</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                SEPTIMA: DECLARACIÓN COMÚN</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Los comparecientes, libre y voluntariamente aceptan y
                ratifican el contenido de la presente Acta en todas sus partes por estar dispuestas en seguridad de sus
                intereses.</p>

            <p style="text-align: justify; margin-top: 30px;">Para constancia suscriben los comparecientes en dos (2)
                ejemplares de igual validez.</p>
        </div>

        <!-- ======================= SECCIÓN DE FIRMAS (ADAPTADA) ======================= -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 80px;">
            <tbody>
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top; padding: 0 10px;">
                        <p
                            style="border-top: 1px solid #333; padding-top: 10px; margin-top: 60px; margin-bottom: 5px; font-size: 11pt; text-align: center;">
                            JOHN PATRICIO NARVAEZ ABAD
                        </p>
                        <p style="margin: 5px 0; font-size: 11pt; text-align: center;">CONSTRUCCIONES PRIME JP S.A</p>
                        <p style="margin: 5px 0; font-size: 11pt; text-align: center;"><strong
                                style="font-weight: bold;">VENDEDOR</strong></p>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top; padding: 0 10px;">
                        <p
                            style="border-top: 1px solid #333; padding-top: 10px; margin-top: 60px; margin-bottom: 5px; font-size: 11pt; text-align: center;">
                            {{ $cliente->nombre_completo }}
                        </p>
                        <p style="margin: 5px 0; font-size: 11pt; text-align: center;"><strong
                                style="font-weight: bold;">CI: {{ $cliente->documento }}</strong></p>
                        <p style="margin: 5px 0; font-size: 11pt; text-align: center;">{{ $cliente->nombre }}</p>
                        <p style="margin: 5px 0; font-size: 11pt; text-align: center;"><strong
                                style="font-weight: bold;">COMPRADOR</strong></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
