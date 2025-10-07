<h3>CONTRATO DE PRESTACIÓN DE SERVICIOS</h3>

<p>
    Conste por el presente documento, el Contrato de Prestación de Servicios que celebran, de una parte,
    <strong>MI EMPRESA S.A.C.</strong>, con RUC N° 123456789, con domicilio en Calle Falsa 123,
    debidamente representada por su Gerente General, el Sr. Administrador del Sistema; y de la otra parte,
    <strong>{{ $cliente->nombre_completo ?? '[NOMBRE DEL CLIENTE]' }}</strong>,
    identificado con DNI N° <strong>{{ $cliente->documento ?? '[DNI DEL CLIENTE]' }}</strong>,
    con domicilio en <strong>{{ $cliente->direccion ?? '[DIRECCIÓN DEL CLIENTE]' }}</strong>,
    en adelante denominado "EL CLIENTE".
</p>

<h4>CLÁUSULA PRIMERA: ANTECEDENTES</h4>
<p>
    MI EMPRESA es una persona jurídica dedicada a la prestación de servicios de desarrollo de software...
    (Aquí va todo el texto fijo del contrato)
</p>

<h4>CLÁUSULA SEGUNDA: OBJETO DEL CONTRATO</h4>
<p>
    Por el presente contrato, MI EMPRESA se obliga a prestar a EL CLIENTE los servicios de...
    (Esta parte podría ser editable por el usuario)
</p>

<br><br>
<p>Firmado en la ciudad de Lima, a los {{ now()->format('d') }} días del mes de {{ now()->format('F') }} del
    {{ now()->format('Y') }}.</p>
