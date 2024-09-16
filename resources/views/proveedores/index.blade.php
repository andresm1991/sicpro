@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="mi">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Proveedores registrados</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ encrypted_route('sistema.proveedor.create', ['menu_id' => $menu_id]) }}"
                                    class="btn btn-dark btn-sm">
                                    <i class="fa-light fa-user-plus"></i> Nuevo Proveedor
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
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">RUC-CI</th>
                                    <th scope="col">Nombre</th>
                                    @if ($slug != 'meteriales.herramientas')
                                        @switch($slug)
                                            @case('mano.obra')
                                                <th scope="col">
                                                    Categoría
                                                </th>
                                                <th scope="col">
                                                    Calificación
                                                </th>
                                            @break

                                            @case('profecionales')
                                                <th scope="col">
                                                    Especialidad
                                                </th>
                                            @break

                                            @default
                                                Producto
                                        @endswitch
                                    @endif
                                    <th scope="col">Teléfono 1</th>
                                    <th scope="col">Teléfono 2</th>
                                    <th scope="col">Teléfono 3</th>
                                    <th scope="col">Correo</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proveedores as $proveedor)
                                    <tr id="{{ $proveedor->id }}">
                                        <td class="align-middle">{{ $proveedor->documento }}</td>
                                        <td class="align-middle text-capitalize">{{ $proveedor->razon_social }}</td>
                                        @if ($slug != 'meteriales.herramientas')
                                            <td class="align-middle text-capitalize">
                                                @foreach ($proveedor->proveedor_articulos as $proveedor_articulo)
                                                    {{ $proveedor_articulo->articulo->descripcion }}
                                                @endforeach
                                            </td>
                                            <td class="align-middle text-capitalize">
                                                <div class="progress">
                                                    <div class="progress-bar @if ($proveedor->calificacion <= 3) bg-danger @elseif ($proveedor->calificacion <= 7) bg-warning @else bg-success @endif  "
                                                        role="progressbar"
                                                        style="width: {{ ($proveedor->calificacion / 10) * 100 }}%;"
                                                        aria-valuenow="{{ $proveedor->calificacion }}" aria-valuemin="0"
                                                        aria-valuemax="10">
                                                        {{ $proveedor->calificacion }}/10
                                                    </div>
                                                </div>

                                            </td>
                                        @endif
                                        @foreach (explode_param($proveedor->telefono) as $telefono)
                                            <td class="align-middle">{{ $telefono }}</td>
                                        @endforeach
                                        <td class="align-middle">{{ $proveedor->correo }}</td>
                                        <td class="align-middle table-actions">
                                            <div class="action-buttons">
                                                <a href="{{ route('sistema.proveedor.edit', ['menu_id' => Crypt::encrypt($menu_id), 'proveedor' => $proveedor->id]) }}"
                                                    class="btn btn-secondary btn-sm btn-space"><i
                                                        class="fa-light fa-pen-to-square"></i> Editar</a>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm delete-proveedor"
                                                    id="{{ $proveedor->id }}"><i class="fa-solid fa-trash-can"></i>
                                                    Eliminar</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>

                    @include('partials.pagination', ['paginator' => $proveedores, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script src="{{ asset('js/proveedor_scripts.js') }}"></script>
@endsection
