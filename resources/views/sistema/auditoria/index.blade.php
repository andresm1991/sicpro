@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            @include('partials.alerts')
            <div class="card">
                <div class="card-body ">
                    <form method="GET" action="{{ route('sistema.auditoria.index') }}">
                        <div class="row">
                            <div class="col-md-2 col-12">
                                <div class="form-group">
                                    <label>Base</label>
                                    <select name="base" class="form-control form-control-sm" onchange="this.form.submit()">
                                        <option value="principal" {{ ($base ?? 'principal') === 'principal' ? 'selected' : '' }}>Principal</option>
                                        <option value="jp" {{ ($base ?? 'principal') === 'jp' ? 'selected' : '' }}>JP Limpieza</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-12">
                                <div class="form-group">
                                    <label>Entidad</label>
                                    <select name="entidad" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        @foreach ($entidades as $entidad)
                                            <option value="{{ $entidad }}" {{ request('entidad') === $entidad ? 'selected' : '' }}>{{ $entidad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-12">
                                <div class="form-group">
                                    <label>Acción</label>
                                    <select name="accion" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        @foreach ($acciones as $accion)
                                            <option value="{{ $accion }}" {{ request('accion') === $accion ? 'selected' : '' }}>{{ $accion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-12">
                                <div class="form-group">
                                    <label>ID usuario</label>
                                    <input type="number" name="user_id" class="form-control form-control-sm"
                                        value="{{ request('user_id') }}" placeholder="ID...">
                                </div>
                            </div>
                            <div class="col-md-2 col-12">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" name="desde" class="form-control form-control-sm"
                                        value="{{ request('desde') }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-12">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date" name="hasta" class="form-control form-control-sm"
                                        value="{{ request('hasta') }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-12">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-dark btn-sm">
                                            <i class="fal fa-search"></i> Filtrar
                                        </button>
                                        <a href="{{ route('sistema.auditoria.index') }}" class="btn btn-secondary btn-sm">
                                            Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group form-search form-icon p-0">
                                    <i class="fal fa-search fa-lg form-control-icon"></i>
                                    <input type="text" name="q" class="form-control form-control-round form-control-sm"
                                        placeholder="Buscar en el mensaje..." value="{{ request('q') }}">
                                </div>
                            </div>
                        </div>
                    </form>
                    @if (($base ?? 'principal') === 'jp')
                        <div class="alert alert-info py-2">
                            Estas filas viven en la base de JP Limpieza (<code>jp_limpieza</code>), no en la base principal.
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Usuario</th>
                                    <th scope="col">Acción</th>
                                    <th scope="col">Entidad</th>
                                    <th scope="col">Mensaje</th>
                                    <th scope="col">IP</th>
                                    <th scope="col">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="align-middle">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '' }}</td>
                                        <td class="align-middle">{{ $log->user ? $log->user->nombre : '(usuario eliminado)' }}</td>
                                        <td class="align-middle">{{ $log->accion ?? '—' }}</td>
                                        <td class="align-middle">
                                            @if ($log->entidad)
                                                {{ $log->entidad }}@if (!is_null($log->entidad_id)) #{{ $log->entidad_id }}@endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ $log->message }}</td>
                                        <td class="align-middle">{{ $log->ip ?? '—' }}</td>
                                        <td class="align-middle">
                                            @if (!empty($log->valores_antes) || !empty($log->valores_despues))
                                                <details>
                                                    <summary>Ver cambios</summary>
                                                    @if (!empty($log->valores_antes))
                                                        <strong>Antes:</strong>
                                                        <pre class="mb-1">{{ json_encode($log->valores_antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @endif
                                                    @if (!empty($log->valores_despues))
                                                        <strong>Después:</strong>
                                                        <pre class="mb-0">{{ json_encode($log->valores_despues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @endif
                                                </details>
                                            @else
                                                —
                                            @endif
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

                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
