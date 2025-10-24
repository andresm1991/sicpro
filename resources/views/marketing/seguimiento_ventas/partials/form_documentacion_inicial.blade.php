<div class="row">
    <label for="" class="col-sm-6 col-form-label">Checklist de Documentos</label>
    <div class="col-sm-6 col-12  d-flex justify-content-end align-items-center">
        <label for="forCheckbox" class="col-form-label mr-3">Etapa completada</label>
        <div class="checkbox-wrapper-8">
            <input class="tgl tgl-skewed completar-etapa" name="etapa_documentacion_completa"
                id="etapa-documentacion-completa" type="checkbox" value="1" @checked($seguimientoVenta->isEtapaCompleta('Documentación inicial')) />
            <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="etapa-documentacion-completa"></label>
        </div>
    </div>

    <div class="col-12">
        <ul class="list-group mb-3" id="lista-documentacion">
            @forelse($seguimientoVenta->documentacionItems as $item)
                <li class="list-group-item d-flex justify-content-between align-items-center"
                    id="doc-item-{{ $item->id }}">
                    <div class="col-sm-8 col-12">
                        <span class="item-nombre">{{ $item->nombre }}</span>
                        <small class="text-muted d-block item-observaciones">{{ $item->observaciones }}</small>
                    </div>
                    <div class="col-sm-2 col-12 item-estado">
                        {{-- Estado con badges de colores --}}
                        @if ($item->estado == 'pendiente')
                            <span class="badge badge-warning ">{{ $item->estado }}</span>
                        @elseif ($item->estado == 'entregado')
                            <span class="badge badge-info">{{ $item->estado }}</span>
                        @elseif ($item->estado == 'en_revision')
                            <span class="badge badge-secondary">{{ $item->estado }}</span>
                        @elseif ($item->estado == 'aprobado')
                            <span class="badge badge-success">{{ $item->estado }}</span>
                        @endif
                    </div>
                    <div class="col-sm-2 col-12 d-flex justify-content-end">
                        {{-- NUEVOS BOTONES DE ACCIÓN --}}
                        <button class="btn btn-sm btn-secondary edit-item-btn mr-2" data-toggle="modal"
                            data-backdrop="static" data-keyboard="false" data-target="#editItemModal"
                            data-item-id="{{ $item->id }}" data-item-nombre="{{ $item->nombre }}"
                            data-item-estado="{{ $item->estado }}" data-item-observaciones="{{ $item->observaciones }}"
                            data-update-url="{{ route('marketing.seguimiento.ventas.documentacion.items.update', $item) }}">
                            Editar
                        </button>

                        <button class="btn btn-sm btn-danger delete-item-btn" data-item-id="{{ $item->id }}"
                            data-delete-url="{{ route('marketing.seguimiento.ventas.documentacion.items.destroy', $item) }}">
                            Eliminar
                        </button>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-danger" id="empty-doc-item">Aún no se han agregado documentos a la
                    checklist.</li>
            @endforelse
        </ul>

        @isset($seguimientoVenta->id)
            {!! Form::open([
                'route' => ['marketing.seguimiento.ventas.agregar.item.documentacion', $seguimientoVenta->id],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_agregar_documento',
            ]) !!}
            <div class="input-group">
                <input type="text" name="nombre" id="nombre-documento-input" class="form-control"
                    placeholder="Nombre del nuevo documento" required>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-dark">Agregar a Checklist</button>
                </div>
            </div>
            {!! Form::close() !!}
        @endisset

    </div>

</div>
