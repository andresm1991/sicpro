@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container d-flex align-items-center justify-content-center full-height">
            <div class="row">
                <div class="max-auto">
                    <a href="{{ route('administrativo.adquisiciones', 'operativo') }}" class="nodo">
                        <img src="{{ asset('images/icons/adquisicion.png') }}" alt="">
                        <span>Adquisiciones Operativas</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('administrativo.adquisiciones', 'administrativo') }}" class="nodo">
                        <img src="{{ asset('images/icons/adquisicion.png') }}" alt="">
                        <span>Adquisiciones Administrativas</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('administrativo.index.contratistas') }}" class="nodo">
                        <img src="{{ asset('images/icons/trabajadores.png') }}" alt="">
                        <span>Contratistas</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('administrativo.index.mano.obra') }}" class="nodo">
                        <img src="{{ asset('images/icons/mano_obra.png') }}" alt="">
                        <span>Mano de Obra</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('administrativo.prestamos.index') }}" class="nodo">
                        <img src="{{ asset('images/icons/passive-income.png') }}" alt="">
                        <span>Prestamos</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('administrativo.resumen.pagos.semanal.index') }}" class="nodo">
                        <img src="{{ asset('images/svg/dar-dinero.svg') }}" alt="">
                        <span>resumen pagos semanal</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('administrativo.caja.index') }}" class="nodo">
                        <img src="{{ asset('images/svg/flujo-de-efectivo.svg') }}" alt="">
                        <span>caja</span>
                    </a>
                </div>

            </div>
        </div>
    </section>
@endsection
