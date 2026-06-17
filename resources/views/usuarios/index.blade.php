@extends('layouts.app')

@section('title', ($tituloGestion ?? 'Gestión de usuarios').' | AgroFusion')
@section('page_title', $tituloGestion ?? 'Gestión de usuarios')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Gestión de usuarios</li>
@endsection

@push('styles')
@include('partials.modulo-usuarios-styles')
<style>
.usu-index-page { max-width: 1200px; margin: 0 auto; }
.usu-index-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 991px) { .usu-index-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px) { .usu-index-stats { grid-template-columns: 1fr; } }
.usu-index-stat {
    border-radius: 14px;
    padding: 1.1rem 1.2rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}
.usu-index-stat h3 { font-size: 1.65rem; font-weight: 700; margin: 0 0 0.1rem; position: relative; z-index: 1; }
.usu-index-stat p { margin: 0; font-size: 0.85rem; opacity: 0.95; position: relative; z-index: 1; }
.usu-index-stat i {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    font-size: 2.2rem; opacity: 0.22;
}
.usu-index-stat.green { background: linear-gradient(135deg, #2c5530, #4a7c59); }
.usu-index-stat.blue { background: linear-gradient(135deg, #17a2b8, #20c997); }
.usu-index-stat.yellow { background: linear-gradient(135deg, #e0a800, #ffc107); color: #1a252f; }
.usu-index-stat.purple { background: linear-gradient(135deg, #6f42c1, #8e64e8); }
.usu-index-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e8ecef;
    box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}
.usu-index-card-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1.1rem 1.35rem;
    background: linear-gradient(135deg, #f8fafc, #fff);
    border-bottom: 1px solid #eef1f4;
}
.usu-index-card-head h2 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.usu-index-card-head h2 i { color: #2c5530; }
.usu-index-head-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
.usu-index-count {
    font-size: 0.82rem;
    color: #64748b;
    background: #f1f5f9;
    border-radius: 999px;
    padding: 0.35rem 0.85rem;
    font-weight: 600;
}
.usu-index-btn-new {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    border: none;
    color: #fff;
    border-radius: 10px;
    padding: 0.45rem 1rem;
    font-weight: 600;
    font-size: 0.88rem;
    box-shadow: 0 3px 10px rgba(44, 85, 48, 0.2);
}
.usu-index-btn-new:hover { color: #fff; opacity: 0.95; }
.usu-index-filtros {
    padding: 1.1rem 1.35rem;
    background: #f8fafc;
    border-bottom: 1px solid #eef1f4;
}
.usu-index-filtros .form-control {
    border-radius: 10px;
    border-color: #dde3ea;
    font-size: 0.9rem;
}
.usu-index-filtros .form-control:focus {
    border-color: #4a7c59;
    box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.12);
}
.usu-index-filtros label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 0.35rem;
}
.usu-index-filtro-actions .btn {
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
}
.usu-index-filtro-actions .btn-apply {
    background: #2c5530;
    border-color: #2c5530;
    color: #fff;
}
.usu-index-chip-lote {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #ecfdf5;
    color: #2c5530;
    border: 1px solid #a7d7b5;
    border-radius: 999px;
    padding: 0.3rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.5rem;
}
.usu-index-table thead th {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    font-weight: 700;
    padding: 0.85rem 1rem;
    white-space: nowrap;
}
.usu-index-table tbody td {
    padding: 0.9rem 1rem;
    vertical-align: middle;
    border-color: #f1f5f9;
    font-size: 0.9rem;
}
.usu-index-table tbody tr:hover { background: #f8fbf8; }
.usu-index-table .user-cell strong { color: #1e293b; }
.usu-index-table .user-cell .handle { color: #64748b; font-size: 0.82rem; }
.usu-index-badge-rol {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.78rem;
    padding: 0.3em 0.65em;
    border-radius: 6px;
}
.usu-index-lotes-count {
    font-size: 0.82rem;
    color: #64748b;
    white-space: nowrap;
}
.usu-index-lotes-count strong { color: #2c5530; }
.usu-index-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    flex-wrap: nowrap;
    vertical-align: middle;
}
.usu-index-actions form {
    display: inline-flex;
    margin: 0;
    padding: 0;
    line-height: 1;
}
.usu-index-actions .btn {
    width: 2rem;
    height: 2rem;
    padding: 0;
    margin: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    flex-shrink: 0;
    line-height: 1;
}
.usu-index-actions .btn i {
    font-size: 0.8rem;
    margin: 0;
}
.usu-index-table td.usu-index-actions-cell {
    white-space: nowrap;
    width: 1%;
}
.usu-index-empty {
    padding: 3rem 1.5rem;
    text-align: center;
    color: #94a3b8;
}
.usu-index-empty i { font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.45; display: block; }
.usu-index-footer {
    padding: 0.85rem 1.35rem;
    background: #fafbfc;
    border-top: 1px solid #eef1f4;
}
</style>
@endpush

@section('content')
@php $modoJefe = $modoJefe ?? false; @endphp
<div class="modulo-usu usu-index-page">

    <div class="usu-index-stats">
        <div class="usu-index-stat green">
            <h3>{{ $stats['total'] ?? 0 }}</h3>
            <p>{{ $modoJefe ? 'Total empleados' : 'Total usuarios' }}</p>
            <i class="fas fa-users"></i>
        </div>
        <div class="usu-index-stat blue">
            <h3>{{ $stats['activos'] ?? 0 }}</h3>
            <p>Activos</p>
            <i class="fas fa-user-check"></i>
        </div>
        @unless($modoJefe)
        <div class="usu-index-stat yellow">
            <h3>{{ $stats['pendientes'] ?? 0 }}</h3>
            <p>Pendientes</p>
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="usu-index-stat purple">
            <h3>{{ $stats['roles'] ?? 0 }}</h3>
            <p>Roles</p>
            <i class="fas fa-user-shield"></i>
        </div>
        @endunless
    </div>

    <div class="usu-index-card">
        <div class="usu-index-card-head">
            <h2><i class="fas fa-users"></i> {{ $tituloGestion ?? 'Usuarios del sistema' }}</h2>
            <div class="usu-index-head-actions">
                <span class="usu-index-count">{{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'registro' : 'registros' }}</span>
                @can('usuarios.create')
                <a href="{{ route('gestion.create') }}" class="btn usu-index-btn-new">
                    <i class="fas fa-user-plus mr-1"></i> Nuevo usuario
                </a>
                @endcan
            </div>
        </div>

        <div class="usu-index-filtros">
            <form method="GET" action="{{ route('gestion.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label for="buscar">Buscar</label>
                        <input type="text" id="buscar" name="buscar" class="form-control"
                            value="{{ request('buscar') }}" placeholder="Nombre, correo, usuario…">
                    </div>
                    @unless($modoJefe)
                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <label for="rol">Rol</label>
                        <select name="rol" id="rol" class="form-control">
                            <option value="">Todos</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(request('rol') == $rol->id)>{{ \App\Support\UsuarioRol::etiquetaRol($rol->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                            <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option>
                        </select>
                    </div>
                    @endunless
                    @if($modoJefe && $lotes->isNotEmpty())
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        @include('partials.selector-catalogo', [
                            'id' => 'usu_filtro_lote',
                            'name' => 'lote',
                            'value' => request('lote'),
                            'labelSelected' => $loteSeleccionado?->nombre ?? '',
                            'endpoint' => route('catalogo-selector.lotes'),
                            'title' => 'Filtrar por lote asignado',
                            'label' => '<i class="fas fa-map-marked-alt mr-1"></i> Lote asignado',
                            'icon' => 'fa-map-marked-alt',
                            'searchPlaceholder' => 'Nombre del lote…',
                            'searchLabel' => 'Buscar lote',
                            'allowEmpty' => true,
                            'emptyLabel' => 'Todos los lotes',
                            'placeholderEmpty' => 'Todos los lotes',
                            'modalIcon' => 'fa-map-marked-alt',
                            'rowIcon' => 'fa-seedling',
                            'colNombre' => 'Lote',
                            'colDetalle' => 'Cultivo / responsable',
                            'variant' => 'filtros',
                        ])
                    </div>
                    @elseif(! $modoJefe)
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        @include('partials.selector-catalogo', [
                            'id' => 'usu_filtro_lote',
                            'name' => 'lote',
                            'value' => request('lote'),
                            'labelSelected' => $loteSeleccionado?->nombre ?? '',
                            'endpoint' => route('catalogo-selector.lotes'),
                            'title' => 'Filtrar por lote asignado',
                            'label' => '<i class="fas fa-map-marked-alt mr-1"></i> Lote asignado',
                            'icon' => 'fa-map-marked-alt',
                            'searchPlaceholder' => 'Nombre del lote…',
                            'searchLabel' => 'Buscar lote',
                            'allowEmpty' => true,
                            'emptyLabel' => 'Todos los lotes',
                            'placeholderEmpty' => 'Todos los lotes',
                            'modalIcon' => 'fa-map-marked-alt',
                            'rowIcon' => 'fa-seedling',
                            'colNombre' => 'Lote',
                            'colDetalle' => 'Cultivo / responsable',
                            'variant' => 'filtros',
                        ])
                    </div>
                    @endif
                    <div class="col-lg-{{ $modoJefe ? '3' : '2' }} col-md-12 mb-2 mb-lg-0 usu-index-filtro-actions d-flex" style="gap:6px;">
                        <button type="submit" class="btn btn-apply flex-grow-1">
                            <i class="fas fa-search mr-1"></i> Aplicar
                        </button>
                        <a href="{{ route('gestion.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
                @if($loteSeleccionado)
                <div class="usu-index-chip-lote">
                    <i class="fas fa-filter"></i>
                    Responsables del lote: <strong>{{ $loteSeleccionado->nombre }}</strong>
                    <a href="{{ route('gestion.index', request()->except('lote')) }}" class="ml-2 text-dark" title="Quitar filtro">&times;</a>
                </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table usu-index-table mb-0">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th class="text-center" style="width:70px">Lotes</th>
                        <th class="text-center" style="width:90px">Estado</th>
                        <th class="text-center usu-index-actions-cell">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    @php $estadoCuenta = $usuario->estado_cuenta ?? \App\Support\CuentaEstado::APROBADO; @endphp
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $usuario->usuarioid }}</td>
                        <td>
                            <div class="d-flex align-items-center user-cell">
                                <div class="avatar-circle {{ $estadoCuenta === \App\Support\CuentaEstado::PENDIENTE ? 'pendiente' : 'activo' }} mr-2">
                                    {{ strtoupper(substr($usuario->nombre, 0, 1).substr($usuario->apellido, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $usuario->nombre }} {{ $usuario->apellido }}</strong>
                                    <div class="handle">{{ '@'.$usuario->nombreusuario }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-break">{{ $usuario->email }}</td>
                        <td class="text-muted">{{ $usuario->telefono ?: '—' }}</td>
                        <td>
                            @forelse($usuario->roles as $role)
                                <span class="usu-index-badge-rol">{{ \App\Support\UsuarioRol::etiquetaRol($role->name) }}</span>
                            @empty
                                <span class="text-muted small">Sin rol</span>
                            @endforelse
                        </td>
                        <td class="text-center usu-index-lotes-count">
                            <strong>{{ $usuario->lotes_count }}</strong>
                        </td>
                        <td class="text-center">
                            @if($estadoCuenta === \App\Support\CuentaEstado::PENDIENTE)
                                <span class="badge badge-warning px-2 py-1">Pendiente</span>
                            @else
                                <span class="badge badge-success px-2 py-1">Activo</span>
                            @endif
                        </td>
                        <td class="text-center usu-index-actions-cell">
                            <div class="usu-index-actions" role="group" aria-label="Acciones">
                                @can('usuarios.view')
                                <a href="{{ route('gestion.show', $usuario) }}" class="btn btn-outline-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @if($estadoCuenta !== \App\Support\CuentaEstado::PENDIENTE)
                                    @can('usuarios.update')
                                    <a href="{{ route('gestion.edit', $usuario) }}" class="btn btn-outline-success" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('usuarios.delete')
                                    <form action="{{ route('gestion.usuario.destroy', $usuario) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="usu-index-empty">
                                <i class="fas fa-users"></i>
                                @if($loteSeleccionado)
                                    No hay usuarios asignados al lote «{{ $loteSeleccionado->nombre }}».
                                @else
                                    No hay usuarios que coincidan con los filtros.
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
        <div class="usu-index-footer d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-2 mb-md-0">
                Mostrando {{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }} de {{ $usuarios->total() }}
            </small>
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
