@extends('layouts.app')
@section('title', $title_page)
@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h4 class="mt-2 font-weight-bold">Configuraciones</h4>
                </li>
            </ul>
            <div class="card-body ">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <a href=""
                                class="btn btn-dark btn-sm">
                                <i class="fa-light fa-user-plus"></i> Nuevo
                            </a>
                        </div>
                    </div>
                    <div class="col-md-8 col-12 ">
                        <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                            <i class="fal fa-search fa-lg form-control-icon"></i>
                            <input type="text" name="proveedor_search" class="form-control form-control-round"
                                placeholder="Buscar proveedor....">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h3>Category List</h3>
                        <ul id="tree1">
                            @foreach($categories as $category)
                                <li>
                                    {{ $category->descripcion }}
                                    @if(count($category->childs))
                                        @include('configuracion.manageChild',['childs' => $category->childs])
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="/js/treeview.js"></script>
@endsection