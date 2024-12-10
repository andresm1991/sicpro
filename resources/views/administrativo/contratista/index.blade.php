@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        @include('partials.alerts')
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Pendientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Completas</a>
                    </li>
                </ul><!--.ul-->
                <div class="tab-content">
                    <div class="tab-pane active" id="tabs-1" role="tabpanel">
                        @include('administrativo.contratista.views.list_pendientes')
                    </div>
                    <div class="tab-pane" id="tabs-2" role="tabpanel">
                        @include('administrativo.contratista.views.list_completas')
                    </div>
                </div><!--.tab-content-->
            </div>
        </div>
        
    </section>

@endsection

@section('scripts')
    
@endsection
