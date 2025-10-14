<div class="row">
    <label for="" class="col-sm-6 col-form-label">Checklist Escrituración</label>
    <div class="col-sm-6 col-12  d-flex justify-content-end align-items-center">
        <label for="forCheckbox" class="col-form-label mr-3">Etapa completada</label>
        <div class="checkbox-wrapper-8">
            <input class="tgl tgl-skewed" name="etapa_escrituracion_completa" id="etapa-escrituracion-completa"
                type="checkbox" value="0" />
            <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="etapa-escrituracion-completa"></label>
        </div>
    </div>

    <div class="col-12">
        <ul class="list-group mb-3" id="lista-escrituracion">
            @forelse($seguimientoVenta->escrituracionItems as $item)
                <li class="list-group-item d-flex justify-content-between align-items-center"
                    id="doc-escrituracion-{{ $item->id }}">
                    <div class="col-sm-8 col-12">
                        <span class="item-nombre">{{ $item->nombre }}</span>
                        <small class="text-muted d-block item-observaciones">{{ $item->observaciones }}</small>
                    </div>
                    <div class="col-sm-2 col-12 item-estado">
                        {{-- Estado con badges de colores --}}
                        @if ($item->completado)
                            <span class="badge badge-success">Completado</span>
                        @else
                            <span class="badge badge-warning ">Pendiente</span>
                        @endif
                    </div>
                    <div class="col-sm-2 col-12 d-flex justify-content-end">
                        {{-- NUEVOS BOTONES DE ACCIÓN --}}
                        <button class="btn btn-sm btn-secondary edit-item-btn mr-2" data-toggle="modal"
                            data-backdrop="static" data-keyboard="false" data-target="#editItemModal"
                            data-item-id="{{ $item->id }}" data-item-nombre="{{ $item->nombre }}"
                            data-item-estado="{{ $item->completado ? 1 : 0 }}"
                            data-item-observaciones="{{ $item->observaciones }}"
                            data-update-url="{{ route('marketing.seguimiento.ventas.escrituracion.items.update', $item) }}">
                            Editar
                        </button>

                        <button class="btn btn-sm btn-danger delete-item-btn" data-item-id="{{ $item->id }}"
                            data-delete-url="{{ route('marketing.seguimiento.ventas.escrituracion.items.destroy', $item) }}">
                            Eliminar
                        </button>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-danger" id="empty-escrituracion-item">Aún no se han agregado documentos
                    a la
                    checklist.</li>
            @endforelse
        </ul>

        @isset($seguimientoVenta->id)
            {!! Form::open([
                'route' => ['marketing.seguimiento.ventas.agregar.item.escrituracion', $seguimientoVenta->id],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_agregar_escrituracion',
            ]) !!}
            <div class="input-group">
                <input type="text" name="nombre" id="nombre-escituracion-input" class="form-control"
                    placeholder="Nombre del nuevo documento" required>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-dark">Agregar a Checklist</button>
                </div>
            </div>
            {!! Form::close() !!}
        @endisset

    </div>

</div>
