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
    <script src="{{ asset('js/proceso_ventas.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection
