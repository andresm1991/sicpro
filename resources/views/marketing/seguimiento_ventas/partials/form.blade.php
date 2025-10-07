<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Reserva</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Documentación inicial</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Peritaje y aprobación de crédito</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabs-4" role="tab">Escrituración</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabs-5" role="tab">Desembolso</a>
            </li>
        </ul><!--.ul-->
        <div class="tab-content">
            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                @include('marketing.seguimiento_ventas.partials.form_reserva')
            </div>
            <div class="tab-pane" id="tabs-2" role="tabpanel">
                @include('marketing.seguimiento_ventas.partials.form_documentacion_inicial')
            </div>
            <div class="tab-pane" id="tabs-3" role="tabpanel">
                @include('marketing.seguimiento_ventas.partials.form_peritaje_aprobacion')
            </div>
            <div class="tab-pane" id="tabs-4" role="tabpanel">
                @include('marketing.seguimiento_ventas.partials.form_escrituracion')
            </div>
            <div class="tab-pane" id="tabs-5" role="tabpanel">
                @include('marketing.seguimiento_ventas.partials.form_desembolso')
            </div>
        </div><!--.tab-content-->
    </div> <!--.col-12-->

</div><!--.row-->

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.custom-file-input').on('change', function(event) {
                // Obtenemos la referencia al label asociado con este input
                var label = $(this).next('.custom-file-label');

                // Guardamos el texto por defecto del label por si lo necesitamos
                // Para este caso, lo escribimos directamente, pero podría leerse del label.
                var defaultLabelText = 'Seleccionar archivo...';

                // Comprobamos si el usuario ha seleccionado algún archivo
                if (event.target.files.length > 0) {
                    // Si SÍ seleccionó, mostramos el nombre del primer archivo
                    var fileName = event.target.files[0].name;
                    label.html(fileName);
                } else {
                    // Si NO seleccionó (canceló), volvemos al texto original
                    label.html(defaultLabelText);
                }
            });
        });
    </script>
@endsection
