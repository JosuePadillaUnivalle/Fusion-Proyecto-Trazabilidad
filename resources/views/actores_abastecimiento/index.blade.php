@extends('layouts.app')

@section('title', 'Actores de abastecimiento | AgroNexus')
@section('page_title', 'Actores de abastecimiento')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Insumos</a></li>
    <li class="breadcrumb-item active">Actores</li>
@endsection

@php
    $pctActivos = $stats['total'] > 0
        ? round(($stats['activos'] / $stats['total']) * 100)
        : 0;
@endphp

@push('styles')
<style>
.page-actores .small-box {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.page-actores .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.page-actores .small-box .icon { font-size: 70px; }
.page-actores .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.page-actores .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.page-actores .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
    color: #fff;
}
.page-actores .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
    color: #fff;
}
.page-actores .card {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.page-actores .tipo-badge-productor { background: #d4edda; color: #155724; }
.page-actores .tipo-badge-proveedor { background: #cce5ff; color: #004085; }
.page-actores .tipo-badge-mixto { background: #e2d5f5; color: #4a235a; }
</style>
@endpush

@section('content')
<div class="page-actores">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible shadow-sm">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5 class="mb-1"><i class="icon fas fa-exclamation-triangle"></i> No se pudo guardar el actor</h5>
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-handshake"></i> Red de abastecimiento</h5>
                Centraliza productores y proveedores en una sola ficha para evitar duplicados y mantener trazabilidad de abastecimiento.
                <div class="mt-2">
                    <a href="{{ route('insumos.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-boxes mr-1"></i> Insumos
                    </a>
                    @canany(['inventario.view', 'inventario.create', 'inventario.update', 'inventario.delete'])
                    <a href="{{ route('recursos-productivos.index') }}" class="btn btn-sm btn-info ml-1">
                        <i class="fas fa-layer-group mr-1"></i> Vista consolidada
                    </a>
                    @endcanany
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Actores registrados</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <span class="small-box-footer">En el catálogo</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['activos'] }}</h3>
                    <p>Actores activos</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <a href="#filtros-actores" class="small-box-footer" id="linkActivos">
                    Ver activos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>{{ $stats['inactivos'] }}</h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon"><i class="fas fa-user-slash"></i></div>
                <a href="#filtros-actores" class="small-box-footer" id="linkInactivos">
                    Ver inactivos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ $stats['tipos'] }}</h3>
                    <p>Tipos de actor</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
                <span class="small-box-footer">Productor · Proveedor · Mixto</span>
            </div>
        </div>
    </div>

    @if ($stats['total'] > 0)
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box shadow-sm mb-0">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-seedling"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Productores</span>
                    <span class="info-box-number">{{ $stats['productores'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm mb-0">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Proveedores</span>
                    <span class="info-box-number">{{ $stats['proveedores'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box shadow-sm mb-0">
                <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-random"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Mixtos</span>
                    <span class="info-box-number">{{ $stats['mixtos'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-secondary mb-0">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-sm-4 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['total'] }}</h5>
                                <span class="description-text text-muted">REGISTROS</span>
                            </div>
                        </div>
                        <div class="col-sm-4 border-right">
                            <div class="description-block mb-0">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-caret-up"></i> {{ $pctActivos }}%
                                </span>
                                <h5 class="description-header">{{ $stats['activos'] }}</h5>
                                <span class="description-text text-muted">ACTIVOS</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['inactivos'] }}</h5>
                                <span class="description-text text-muted">INACTIVOS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card card-success card-outline elevation-2 mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus mr-1 text-success"></i> Nuevo actor de abastecimiento</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('actores-abastecimiento.store') }}">
                @csrf
                <div class="form-row align-items-end">
                    <div class="form-group col-lg-4 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Nombre o razón social</label>
                        <input name="nombre" class="form-control form-control-sm" placeholder="Ej. Cooperativa Valle Verde"
                            value="{{ old('nombre') }}" required maxlength="120">
                    </div>
                    <div class="form-group col-lg-2 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Tipo</label>
                        <select name="tipo_actor" class="form-control form-control-sm" required>
                            <option value="productor" {{ old('tipo_actor') === 'productor' ? 'selected' : '' }}>Productor</option>
                            <option value="proveedor" {{ old('tipo_actor') === 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                            <option value="mixto" {{ old('tipo_actor') === 'mixto' ? 'selected' : '' }}>Mixto</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2 col-md-4 mb-2">
                        <label class="small text-muted mb-1">Teléfono</label>
                        <input name="telefono" class="form-control form-control-sm" placeholder="+591 ..."
                            value="{{ old('telefono') }}" maxlength="30">
                    </div>
                    <div class="form-group col-lg-3 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Email</label>
                        <input name="email" type="email" class="form-control form-control-sm" placeholder="correo@ejemplo.com"
                            value="{{ old('email') }}" maxlength="120">
                    </div>
                    <div class="form-group col-lg-1 col-md-2 mb-2">
                        <button type="submit" class="btn btn-success btn-sm btn-block" title="Crear actor">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-primary card-outline elevation-2" id="filtros-actores">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-address-book mr-1 text-primary"></i> Actores consolidados</h3>
            <span class="badge badge-secondary ml-2">{{ $actores->total() }} registros</span>
        </div>
        <div class="card-body border-bottom pb-2">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="actorSearch" class="form-control"
                            placeholder="Buscar por nombre, teléfono o email...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <select id="actorTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro as $tipo)
                            <option value="{{ strtolower($tipo) }}">{{ ucfirst($tipo) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 mb-2">
                    <select id="actorEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros">
                        <i class="fas fa-eraser mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
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
                        @php
                            $tipoClass = match($actor->tipo_actor) {
                                'proveedor' => 'tipo-badge-proveedor',
                                'mixto' => 'tipo-badge-mixto',
                                default => 'tipo-badge-productor',
                            };
                            $tipoIcon = match($actor->tipo_actor) {
                                'proveedor' => 'truck',
                                'mixto' => 'random',
                                default => 'seedling',
                            };
                        @endphp
                        <tr class="actor-row"
                            data-id="{{ $actor->actorid }}"
                            data-search="{{ strtolower($actor->nombre.' '.($actor->telefono ?? '').' '.($actor->email ?? '')) }}"
                            data-tipo="{{ strtolower($actor->tipo_actor) }}"
                            data-estado="{{ $actor->activo ? 'activo' : 'inactivo' }}"
                            data-nombre="{{ $actor->nombre }}"
                            data-telefono="{{ $actor->telefono }}"
                            data-email="{{ $actor->email }}"
                            data-tipo-texto="{{ ucfirst($actor->tipo_actor) }}"
                            data-activo="{{ $actor->activo ? '1' : '0' }}">
                            <td>
                                <strong class="text-success">{{ $actor->nombre }}</strong>
                            </td>
                            <td>
                                <span class="badge {{ $tipoClass }}">
                                    <i class="fas fa-{{ $tipoIcon }} mr-1"></i>{{ ucfirst($actor->tipo_actor) }}
                                </span>
                            </td>
                            <td>
                                @if($actor->telefono)
                                    <i class="fas fa-phone text-muted mr-1"></i>{{ $actor->telefono }}<br>
                                @endif
                                @if($actor->email)
                                    <i class="fas fa-envelope text-muted mr-1"></i>{{ $actor->email }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($actor->activo)
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Activo</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-ban mr-1"></i>Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                <button type="button" class="btn btn-xs btn-info actor-detalle-btn" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-warning actor-editar-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('actores-abastecimiento.destroy', $actor) }}"
                                    class="d-inline on-submit-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-handshake fa-3x mb-3 text-light d-block"></i>
                                No hay actores registrados. Crea el primero con el formulario superior.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($actores->hasPages())
        <div class="card-footer">
            {{ $actores->links() }}
        </div>
        @endif
    </div>

</div>

<div class="modal fade" id="actorDetalleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-id-card mr-1"></i> Detalle del actor</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8" id="dNombre">—</dd>
                    <dt class="col-sm-4">Tipo</dt>
                    <dd class="col-sm-8" id="dTipo">—</dd>
                    <dt class="col-sm-4">Teléfono</dt>
                    <dd class="col-sm-8" id="dTelefono">—</dd>
                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8" id="dEmail">—</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="actorEditarModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="actorEditarForm">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Editar actor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="nombre" id="eNombre" required maxlength="120">
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select class="form-control form-control-sm" name="tipo_actor" id="eTipo" required>
                            <option value="productor">Productor</option>
                            <option value="proveedor">Proveedor</option>
                            <option value="mixto">Mixto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control form-control-sm" name="telefono" id="eTelefono" maxlength="30">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control form-control-sm" name="email" id="eEmail" maxlength="120">
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="eActivo" name="activo" value="1">
                            <label class="custom-control-label" for="eActivo">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const q = document.getElementById('actorSearch');
    const t = document.getElementById('actorTipo');
    const e = document.getElementById('actorEstado');
    const rows = Array.from(document.querySelectorAll('.actor-row'));
    const editForm = document.getElementById('actorEditarForm');
    const updateUrlTemplate = @json(route('actores-abastecimiento.update', ['actores_abastecimiento' => '__ID__']));

    function filtrar() {
        const vq = (q?.value || '').toLowerCase().trim();
        const vt = (t?.value || '').toLowerCase();
        const ve = (e?.value || '').toLowerCase();
        rows.forEach((tr) => {
            const okQ = !vq || (tr.dataset.search || '').includes(vq);
            const okT = !vt || (tr.dataset.tipo || '') === vt;
            const okE = !ve || (tr.dataset.estado || '') === ve;
            tr.style.display = (okQ && okT && okE) ? '' : 'none';
        });
    }

    q?.addEventListener('keyup', filtrar);
    t?.addEventListener('change', filtrar);
    e?.addEventListener('change', filtrar);

    document.getElementById('btnLimpiarFiltros')?.addEventListener('click', function () {
        if (q) q.value = '';
        if (t) t.value = '';
        if (e) e.value = '';
        filtrar();
    });

    document.getElementById('linkActivos')?.addEventListener('click', function (ev) {
        ev.preventDefault();
        if (e) e.value = 'activo';
        filtrar();
        document.getElementById('filtros-actores')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.getElementById('linkInactivos')?.addEventListener('click', function (ev) {
        ev.preventDefault();
        if (e) e.value = 'inactivo';
        filtrar();
        document.getElementById('filtros-actores')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.querySelectorAll('.actor-detalle-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const tr = this.closest('tr');
            if (!tr) return;
            document.getElementById('dNombre').textContent = tr.dataset.nombre || '—';
            document.getElementById('dTipo').textContent = tr.dataset.tipoTexto || '—';
            document.getElementById('dTelefono').textContent = tr.dataset.telefono || '—';
            document.getElementById('dEmail').textContent = tr.dataset.email || '—';
            $('#actorDetalleModal').modal('show');
        });
    });

    document.querySelectorAll('.actor-editar-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const tr = this.closest('tr');
            if (!tr || !editForm) return;

            const actorId = tr.dataset.id || '';
            editForm.action = updateUrlTemplate.replace('__ID__', actorId);
            document.getElementById('eNombre').value = tr.dataset.nombre || '';
            document.getElementById('eTipo').value = (tr.dataset.tipo || 'productor');
            document.getElementById('eTelefono').value = tr.dataset.telefono || '';
            document.getElementById('eEmail').value = tr.dataset.email || '';
            document.getElementById('eActivo').checked = (tr.dataset.activo || '0') === '1';

            $('#actorEditarModal').modal('show');
        });
    });

    document.querySelectorAll('.on-submit-confirm').forEach((form) => {
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const f = this;
            Swal.fire({
                title: '¿Eliminar actor?',
                text: 'No podrás revertir esto',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    f.submit();
                }
            });
        });
    });

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: '¡Hecho!',
        text: @json(session('success')),
        confirmButtonColor: '#2c5530',
        timer: 3000,
        showConfirmButton: false
    });
    @endif
});
</script>
@endpush
