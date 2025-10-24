<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs" id="etapasTab" role="tablist">
            @foreach (App\Models\Marketing\ProcesoVenta::ETAPAS as $etapa)
                <li class="nav-item">
                    {{-- Añadimos la clase 'active' si la etapa del bucle
                        coincide con la etapa_actual del seguimientoVenta. --}}
                    <a class="nav-link {{ $seguimientoVenta->etapa_actual == $etapa || (empty($seguimientoVenta->etapa_actual) && $loop->first) ? 'active' : ($seguimientoVenta->etapa_actual ? '' : 'disabled') }}"
                        id="tab-{{ Str::slug($etapa) }}" data-toggle="tab" href="#pane-{{ Str::slug($etapa) }}"
                        role="tab" data-etapa-slug="{{ Str::slug($etapa) }}">
                        {{ $etapa }}
                        <i
                            class="fas fa-lock ml-2 {{ $seguimientoVenta->etapa_actual == $etapa || (empty($seguimientoVenta->etapa_actual) && $loop->first) ? ' d-none' : ($seguimientoVenta->etapa_actual ? 'd-none' : '') }}"></i>
                    </a>
                </li>
            @endforeach
        </ul><!--.ul-->
        <div class="tab-content">
            @php
                $partials = [
                    'Reserva' => 'marketing.seguimiento_ventas.partials.form_reserva',
                    'Documentación inicial' => 'marketing.seguimiento_ventas.partials.form_documentacion_inicial',
                    'Peritaje y aprobación de crédito' =>
                        'marketing.seguimiento_ventas.partials.form_peritaje_aprobacion',
                    'Escrituración' => 'marketing.seguimiento_ventas.partials.form_escrituracion',
                    'Desembolso' => 'marketing.seguimiento_ventas.partials.form_desembolso',
                    'Entrega' => 'marketing.seguimiento_ventas.partials.form_acta_entrega',
                ];
            @endphp
            @foreach (App\Models\Marketing\ProcesoVenta::ETAPAS as $etapa)
                <div class="tab-pane {{ $seguimientoVenta->etapa_actual == $etapa || (empty($seguimientoVenta->etapa_actual) && $loop->first) ? 'active show' : '' }}"
                    id="pane-{{ Str::slug($etapa) }}" role="tabpanel">
                    @if (isset($partials[$etapa]))
                        @include($partials[$etapa])
                    @else
                        <p>Contenido para la etapa: {{ $etapa }}</p>
                    @endif
                </div>
            @endforeach
        </div><!--.tab-content-->
    </div> <!--.col-12-->

</div><!--.row-->

<div class="row">
    <div class="col-12">
        @include('marketing.seguimiento_ventas.modals.edit_item_modal')
    </div>
</div>



@section('scripts')
    <script src="{{ asset('js/proceso_ventas.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection
