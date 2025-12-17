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
        <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #eeeeee; padding-bottom: 20px;">
            <tbody>
                <tr>
                    <td style="width: 50%; vertical-align: middle;">
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

        <div style="padding-top: 20px;">
            <h1 style="text-align: center; font-size: 18pt; margin-bottom: 30px; text-transform: uppercase;">Acta de
                Entrega-Recepción de Vivienda</h1>

            <div style="margin-bottom: 20px;">
                <p style="margin: 0;"><strong>Cliente:</strong> {{ $cliente->nombre_completo }}</p>
                <p style="margin: 0;"><strong>Manzana:</strong> Mz 2 Calle B</p>
                <p style="margin: 0;"><strong>Unidad:</strong> {{ $proceso->unidad }}</p>
            </div>

            <p style="text-align: justify; margin-bottom: 15px;">
                En la ciudad de Santo Domingo, a los {{ $fecha_entrega->day }} días del mes de
                {{ $fecha_entrega->translatedFormat('F') }} del año {{ $fecha_entrega->year }}, se suscribe la presente
                ACTA DE ENTREGA-RECEPCIÓN de la vivienda número {{ $proceso->unidad }}, manzana 2 calle B, del CLUB
                PRIVADO CIUDAD CELESTE ubicado en la vía Colorados del Búa, en la
                ciudad de Santo Domingo, al tenor de las siguientes cláusulas:
            </p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                PRIMERA: COMPARECIENTES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Comparecen a la celebración de la presente ACTA DE
                ENTREGA RECEPCIÓN DE VIVIENDA por una parte CONSTRUCCIONES PRIME JP S.A, como a quien en adelante y para
                efectos de la presente Acta se le denominará como el VENDEDOR, y por otra parte el señor
                {{ $cliente->nombre_completo }} con CI: {{ $cliente->cedula }} a quién para efectos del presente
                instrumento se le denominara COMPRADOR. Los comparecientes son mayores de edad, hábiles para contratar y
                obligarse.
            </p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                SEGUNDA: ANTECEDENTES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Con fecha 18 de julio del 2025 se realizó la reserva de
                COMPRAVENTA de la
                vivienda número {{ $proceso->unidad }} manzana 2 calle B. Los precios y formas de pago
                fueron determinados por mutuo acuerdo.</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                TERCERA: DECLARACIONES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Mediante este documento, el COMPRADOR acusa recibo de
                la entrega física del inmueble por parte del VENDEDOR. A partir de la fecha de suscripción de esta Acta,
                la Constructora se deslinda de toda responsabilidad por daños producidos por el uso de la casa posterior
                a esta fecha. Ambas partes declaran que, al momento de esta entrega, el inmueble se encuentra en
                perfecto estado de conservación y funcionamiento, incluyendo:</p>

            <div style="margin-left: 20px;">
                <p style="margin-bottom: 5px;"><strong>1. Infraestructura</strong></p>
                <ul style="margin-top: 0;">
                    <li>Paredes, techos y pisos sin grietas, humedad ni deterioro visible.</li>
                    <li>Pintura en buen estado, reciente o sin manchas evidentes.</li>
                    <li>Estructura sólida y sin filtraciones.</li>
                </ul>

                <p style="margin-bottom: 5px;"><strong>2. Puertas y Ventanas</strong></p>
                <ul style="margin-top: 0;">
                    <li>Todas las puertas y ventanas funcionan correctamente.</li>
                    <li>Cerraduras y bisagras en buen estado.</li>
                    <li>Vidrios completos, sin fisuras ni daños.</li>
                </ul>

                <p style="margin-bottom: 5px;"><strong>3. Instalaciones</strong></p>
                <ul style="margin-top: 0;">
                    <li>Eléctrica: En funcionamiento, sin cables expuestos ni cortocircuitos.</li>
                    <li>Sanitaria: Desagües y grifería sin fugas ni obstrucciones.</li>
                    <li>Iluminación: Todos los interruptores y luminarias en funcionamiento.</li>
                </ul>

                <p style="margin-bottom: 5px;"><strong>4. Mobiliario</strong></p>
                <ul style="margin-top: 0;">
                    <li>Muebles empotrados o accesorios instalados correctamente sin defectos.</li>
                </ul>
            </div>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                CUARTA: DAÑOS</h3>
            <p style="text-align: justify; margin-bottom: 15px;">La constructora se hace responsable por los daños
                ocurridos por defecto de construcción de acuerdo a lo que manda la ley. Los daños que resulten del uso y
                desgaste natural de los bienes no serán responsabilidad de la constructora.</p>
            <p style="text-align: justify; margin-bottom: 15px;">Se recomienda al COMPRADOR, que para realizar cualquier
                modificación ya sea de tipo estructural, mampostería, eléctrico, hidrosanitario, etc. se solicite el
                servicio o la consulta técnica correspondiente al técnico profesional de la constructora; esto con el
                fin de evitar daños en la vivienda ocasionados por la contratación de personal ajeno a la misma. La
                Constructora no se hace responsable de daños en la vivienda si una persona ajena a nuestra empresa
                manipula o cambia cualquier sistema del bien entregado.</p>
            <p style="text-align: justify; margin-bottom: 15px;">Además como constancia de la solicitud de la visita
                técnica se deberá firmar el informe técnico en el que se detalla la explicación a la modificación que se
                desee realizar.</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                QUINTA: GARANTIAS</h3>
            <p style="text-align: justify; margin-bottom: 10px;">En Ecuador, se rigen principalmente por las
                disposiciones de la Ley de Defensa del Consumidor, el Código Civil y normas técnicas específicas de
                construcción. Aquí las garantías a considerar:</p>
            <ul style="margin-bottom: 15px;">
                <li><strong>Estructurales:</strong> Fallas en cimientos, paredes, techos que comprometan la seguridad.
                    (5 AÑOS)</li>
                <li><strong>Filtraciones o humedades:</strong> Cuando son resultado de fallas en la impermeabilización o
                    en el diseño constructivo. (1 AÑO)</li>
                <li><strong>Instalaciones:</strong> Problemas con instalaciones eléctricas, sanitarias o de gas, si no
                    cumplen las normas de seguridad o funcionalidad. (1 AÑO)</li>
                <li><strong>Acabados defectuosos:</strong> Como pisos mal instalados, puertas o ventanas que no
                    funcionan correctamente. (1 AÑO)</li>
            </ul>

            <p style="margin-bottom: 5px;"><strong>Exclusiones de la Garantía</strong></p>
            <ul style="margin-bottom: 15px;">
                <li>Desgaste por uso normal.</li>
                <li>Micro fisuras estéticas: Si no comprometen la estructura ni la funcionalidad del inmueble.</li>
                <li>Daños por uso inadecuado o falta de mantenimiento.</li>
                <li>Alteraciones realizadas por el propietario: Reformas o cambios posteriores que afecten el diseño
                    original.</li>
            </ul>

            <p style="text-align: justify; background-color: #f9f9f9; padding: 10px; border-left: 3px solid #ccc;">
                <strong>NOTA ADICIONAL:</strong> Según el código civil en su artículo 1831 considera que “defectos como
                micro fisuras no se consideran suficientes para afectar la funcionalidad o valor del inmueble” por lo
                que las micro fisuras por asentamiento de la vivienda no serán consideradas en temas de garantías.
            </p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                RECOMENDACIONES PARA MANTENIMIENTO DEL BIEN INMUEBLE</h3>
            <p style="text-align: justify; margin-bottom: 10px;">Tomando en cuenta que la edificación de la vivienda es
                una construcción nueva que por ende pasará por el efecto de ASENTAMIENTO CONSTRUCTIVO se recomienda al
                cumplirse los dos años lo siguiente:</p>
            <ol>
                <li>Mantenimiento preventivo que corresponde a pintura interna y externa (en la cual se corrije las
                    microfisuras preparando la superficie antes de la ejecución de pintura).</li>
                <li>Mantenimiento en cuanto a losas (primero se realizará correctivo de microfisuras en caso de su
                    existencia para proceder a su impermeabilizacion). Además es necesario realizar una limpieza anual
                    para su respectivo mantenimiento por los impactos recibidos y cambios climáticos como agentes
                    existentes del ambiente.</li>
                <li>En cuanto a ventaneria se recomienda revisión y mantenimiento de siliconeado.</li>
            </ol>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                SEXTA: CIRCUNSTANCIAS ADICIONALES</h3>
            <p style="text-align: justify; margin-bottom: 15px;">El pago de las alícuotas se realizará directamente a la
                administración del CLUB PRIVADO CIUDAD CELESTE, así mismo los controles de la puerta de ingreso se
                solicitará únicamente a la administración actual. Junto con este documento se hace la entrega de
                REGLAMENTO INTERNO del CLUB PRIVADO “CIUDAD CELESTE” para su conocimiento y aplicación.</p>

            <h3
                style="font-size: 12pt; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 30px;">
                SEPTIMA: DECLARACIÓN COMÚN</h3>
            <p style="text-align: justify; margin-bottom: 15px;">Los comparecientes, libre y voluntariamente aceptan y
                ratifican el contenido de la presente Acta en todas sus partes por estar dispuestas en seguridad de sus
                intereses.</p>

            <p style="text-align: justify; margin-top: 30px;">Para constancia suscriben los comparecientes en dos (2)
                ejemplares de igual validez.</p>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 60px;">
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
                                style="font-weight: bold;">CI: {{ $cliente->cedula }}</strong></p>
                        <p style="margin: 5px 0; font-size: 11pt; text-align: center;"><strong
                                style="font-weight: bold;">COMPRADOR</strong></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>