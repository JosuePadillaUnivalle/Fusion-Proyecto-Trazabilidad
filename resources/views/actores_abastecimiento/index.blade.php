@extends('layouts.app')

@section('title', 'Actores de abastecimiento | AgroNexus')
@section('page_title', 'Actores de abastecimiento')

@push('styles')
<style>
    .actor-stat { border-radius:14px; padding:20px 22px; color:#fff; position:relative; overflow:hidden; transition:transform .18s,box-shadow .18s; }
    .actor-stat:hover { transform:translateY(-3px); box-shadow:0 12px 28px rgba(0,0,0,.18) !important; }
    .actor-stat .s-icon { position:absolute; right:16px; top:50%; transform:translateY(-50%); font-size:2.2rem; opacity:.22; }
    .actor-stat .s-num  { font-size:2rem; font-weight:800; line-height:1; margin-bottom:4px; }
    .actor-stat .s-lbl  { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.07em; opacity:.88; }
    .actor-stat .s-sub  { font-size:.72rem; opacity:.75; margin-top:6px; }
    .actor-stat.total   { background:linear-gradient(135deg,#065f46,#059669); }
    .actor-stat.activos { background:linear-gradient(135deg,#0e7490,#06b6d4); }
    .actor-stat.inactivos { background:linear-gradient(135deg,#9f1239,#e11d48); }
    .actor-stat.tipos   { background:linear-gradient(135deg,#581c87,#9333ea); }

    .tipo-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:20px; font-size:.72rem; font-weight:700; }
    .tipo-badge.productor { background:#d1fae5; color:#065f46; }
    .tipo-badge.proveedor { background:#dbeafe; color:#1e40af; }
    .tipo-badge.mixto     { background:#ede9fe; color:#5b21b6; }

    .estado-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:20px; font-size:.72rem; font-weight:600; }
    .estado-badge.activo   { background:#d1fae5; color:#065f46; }
    .estado-badge.inactivo { background:#fee2e2; color:#b91c1c; }

    .actor-actions a, .actor-actions button { width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:none; cursor:pointer; font-size:.78rem; transition:all .14s; }
    .action-view   { background:#e0f2fe; color:#0369a1; }
    .action-edit   { background:#fef9c3; color:#854d0e; }
    .action-delete { background:#fee2e2; color:#b91c1c; }
    .actor-actions a:hover, .actor-actions button:hover { filter:brightness(.9); transform:translateY(-1px); }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle mr-2"></i><strong>No se pudo guardar el actor.</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

{{-- Stat boxes --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="actor-stat total">
            <div class="s-num">{{ $stats['total'] }}</div>
            <div class="s-lbl">Actores registrados</div>
            <div class="s-sub">En el catálogo</div>
            <div class="s-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="actor-stat activos">
            <div class="s-num">{{ $stats['activos'] }}</div>
            <div class="s-lbl">Actores activos</div>
            <div class="s-sub">Ver activos →</div>
            <div class="s-icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="actor-stat inactivos">
            <div class="s-num">{{ $stats['inactivos'] }}</div>
            <div class="s-lbl">Inactivos</div>
            <div class="s-sub">Ver inactivos →</div>
            <div class="s-icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="actor-stat tipos">
            <div class="s-num">{{ $stats['tipos'] }}</div>
            <div class="s-lbl">Tipos de actor</div>
            <div class="s-sub">Productor · Proveedor · Mixto</div>
            <div class="s-icon"><i class="fas fa-tags"></i></div>
        </div>
    </div>
</div>

{{-- Create form --}}
@can('inventario.update')
<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fas fa-user-plus text-success mr-2"></i>
        <strong>Nuevo actor de abastecimiento</strong>
        <button class="btn btn-sm btn-link ml-auto p-0 text-muted" id="toggleForm"><i class="fas fa-minus"></i></button>
    </div>
    <div class="card-body" id="formBody">
        <form method="POST" action="{{ route('actores-abastecimiento.store') }}">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Nombre o razón social</label>
                    <input name="nombre" class="form-control" placeholder="Ej. Cooperativa Valle Verde" value="{{ old('nombre') }}" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo_actor" class="form-control" required>
                        <option value="productor" {{ old('tipo_actor') === 'productor' ? 'selected' : '' }}>Productor</option>
                        <option value="proveedor" {{ old('tipo_actor') === 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                        <option value="mixto"     {{ old('tipo_actor') === 'mixto'     ? 'selected' : '' }}>Mixto</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Teléfono</label>
                    <input name="telefono" class="form-control" placeholder="+591 …" value="{{ old('telefono') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" placeholder="correo@ejemplo.com" value="{{ old('email') }}">
                </div>
                <div class="col-md-1 mb-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-success btn-block">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fas fa-handshake text-success mr-2"></i>
        <strong>Actores de abastecimiento</strong>
        <span class="badge badge-secondary ml-1">{{ $actores->count() }} registros</span>
        <div class="ml-auto">
            <input type="text" id="searchActor" class="form-control form-control-sm" placeholder="Buscar…" style="width:180px;">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="actoresTable">
                <thead>
                    <tr>
                        <th>Actor</th>
                        <th>Tipo</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actores as $actor)
                    <tr>
                        <td>
                            <span class="font-weight-600" style="color:#0f172a;">{{ $actor->nombre }}</span>
                        </td>
                        <td>
                            @php
                                $tipoMap = ['productor'=>['class'=>'productor','icon'=>'fa-leaf'],
                                            'proveedor'=>['class'=>'proveedor','icon'=>'fa-truck'],
                                            'mixto'    =>['class'=>'mixto',    'icon'=>'fa-random']];
                                $t = $tipoMap[$actor->tipo_actor] ?? ['class'=>'mixto','icon'=>'fa-circle'];
                            @endphp
                            <span class="tipo-badge {{ $t['class'] }}">
                                <i class="fas {{ $t['icon'] }}"></i>{{ ucfirst($actor->tipo_actor) }}
                            </span>
                        </td>
                        <td style="font-size:.83rem;">
                            @if($actor->telefono)
                                <div><i class="fas fa-phone text-muted mr-1" style="font-size:.7rem;"></i>{{ $actor->telefono }}</div>
                            @endif
                            @if($actor->email)
                                <div><i class="fas fa-envelope text-muted mr-1" style="font-size:.7rem;"></i>{{ $actor->email }}</div>
                            @endif
                            @if(!$actor->telefono && !$actor->email)
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="estado-badge {{ $actor->activo ? 'activo' : 'inactivo' }}">
                                <i class="fas {{ $actor->activo ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                {{ $actor->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="actor-actions d-inline-flex gap-1">
                                @can('inventario.update')
                                <button class="action-edit" data-toggle="modal" data-target="#editModal"
                                    data-id="{{ $actor->actorid }}"
                                    data-nombre="{{ $actor->nombre }}"
                                    data-tipo="{{ $actor->tipo_actor }}"
                                    data-telefono="{{ $actor->telefono }}"
                                    data-email="{{ $actor->email }}"
                                    data-activo="{{ $actor->activo ? '1' : '0' }}"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('inventario.delete')
                                <form method="POST" action="{{ route('actores-abastecimiento.destroy', $actor) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este actor?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-delete" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                            No hay actores registrados. Creá el primero con el formulario superior.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Edit modal --}}
@can('inventario.update')
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2 text-success"></i>Editar actor</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input name="nombre" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Tipo</label>
                                <select name="tipo_actor" id="edit_tipo" class="form-control" required>
                                    <option value="productor">Productor</option>
                                    <option value="proveedor">Proveedor</option>
                                    <option value="mixto">Mixto</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <select name="activo" id="edit_activo" class="form-control">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input name="telefono" id="edit_telefono" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" id="edit_email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@push('scripts')
<script>
$(function () {
    // Inline search
    $('#searchActor').on('input', function () {
        var q = this.value.toLowerCase();
        $('#actoresTable tbody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    // Toggle form
    $('#toggleForm').on('click', function () {
        $('#formBody').slideToggle(180);
        $(this).find('i').toggleClass('fa-minus fa-plus');
    });

    // Edit modal
    $('#editModal').on('show.bs.modal', function (e) {
        var btn = $(e.relatedTarget);
        var id = btn.data('id');
        $('#editForm').attr('action', '/actores-abastecimiento/' + id);
        $('#edit_nombre').val(btn.data('nombre'));
        $('#edit_tipo').val(btn.data('tipo'));
        $('#edit_telefono').val(btn.data('telefono') || '');
        $('#edit_email').val(btn.data('email') || '');
        $('#edit_activo').val(btn.data('activo'));
    });
});
</script>
@endpush
