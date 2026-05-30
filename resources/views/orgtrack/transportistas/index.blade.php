@extends('layouts.app')

@section('title', 'Transportistas | AgroFusion')
@section('page_title', 'Gestión de Transportistas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Transportistas</li>
@endsection

@push('styles')
<style>
.stat-card { border:0; border-radius:14px; overflow:hidden; box-shadow:0 6px 20px rgba(18,38,63,.08); }
.stat-card .card-body { padding:1.1rem 1.4rem; }
.stat-icon { font-size:2rem; opacity:.18; position:absolute; right:14px; top:50%; transform:translateY(-50%); }
.x-card { border:0; border-radius:14px; box-shadow:0 6px 20px rgba(18,38,63,.08); }
.x-card .card-header { background:#fff; border-bottom:1px solid #f0f0f0; border-radius:14px 14px 0 0 !important; padding:.9rem 1.2rem; }
.x-table thead th { background:#f2f7f3; border-bottom:0; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#4a7c59; font-weight:700; padding:.65rem 1rem; }
.x-table tbody td { padding:.7rem 1rem; vertical-align:middle; }
.x-table tbody tr:hover { background:#f8fdf9; }
.badge-activo { background:#e8f5e8; color:#2c5530; font-size:.75rem; padding:.3em .7em; border-radius:20px; font-weight:600; }
.badge-inactivo { background:#fdecea; color:#c62828; font-size:.75rem; padding:.3em .7em; border-radius:20px; font-weight:600; }
.avatar-circle { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#2c5530,#4a7c59); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; }
.search-bar { border-radius:8px 0 0 8px !important; border-right:0 !important; }
.search-btn { border-radius:0 8px 8px 0 !important; background:#2c5530; border-color:#2c5530; color:#fff; }
.search-btn:hover { background:#3a6b40; border-color:#3a6b40; color:#fff; }
.btn-nuevo { background:linear-gradient(135deg,#2c5530,#4a7c59); border:0; border-radius:8px; padding:.45rem 1.1rem; font-weight:600; color:#fff; }
.btn-nuevo:hover { background:linear-gradient(135deg,#3a6b40,#5a8c69); color:#fff; }
.btn-accion { border-radius:6px; font-size:.78rem; padding:.3rem .65rem; }
.empty-state { padding:3rem; text-align:center; color:#adb5bd; }
.empty-state i { font-size:3rem; margin-bottom:.8rem; }
.filter-select { border-radius:8px; font-size:.85rem; height:calc(1.5em + .75rem + 2px); }
</style>
@endpush

@section('content')

{{-- Stats --}}
<div class="row mb-3">
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card position-relative" style="background:linear-gradient(135deg,#2c5530,#4a7c59);">
            <div class="card-body text-white">
                <i class="fas fa-users stat-icon"></i>
                <div class="text-uppercase" style="font-size:.7rem;opacity:.75;letter-spacing:.06em;">Total</div>
                <div style="font-size:1.9rem;font-weight:800;line-height:1.1;">{{ $total }}</div>
                <div style="font-size:.8rem;opacity:.8;">transportistas registrados</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card position-relative" style="background:linear-gradient(135deg,#28a745,#20c997);">
            <div class="card-body text-white">
                <i class="fas fa-check-circle stat-icon"></i>
                <div class="text-uppercase" style="font-size:.7rem;opacity:.75;letter-spacing:.06em;">Activos</div>
                <div style="font-size:1.9rem;font-weight:800;line-height:1.1;">{{ $activos }}</div>
                <div style="font-size:.8rem;opacity:.8;">disponibles ahora</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card position-relative" style="background:linear-gradient(135deg,#fd7e14,#ffc107);">
            <div class="card-body text-white">
                <i class="fas fa-times-circle stat-icon"></i>
                <div class="text-uppercase" style="font-size:.7rem;opacity:.75;letter-spacing:.06em;">Inactivos</div>
                <div style="font-size:1.9rem;font-weight:800;line-height:1.1;">{{ $total - $activos }}</div>
                <div style="font-size:.8rem;opacity:.8;">fuera de servicio</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card position-relative" style="background:linear-gradient(135deg,#17a2b8,#6f42c1);">
            <div class="card-body text-white">
                <i class="fas fa-truck-moving stat-icon"></i>
                <div class="text-uppercase" style="font-size:.7rem;opacity:.75;letter-spacing:.06em;">Resultados</div>
                <div style="font-size:1.9rem;font-weight:800;line-height:1.1;">{{ $transportistas->total() }}</div>
                <div style="font-size:.8rem;opacity:.8;">en esta búsqueda</div>
            </div>
        </div>
    </div>
</div>

{{-- Flash messages --}}
@include('partials.flash-messages')

{{-- Tabla --}}
<div class="card card-outline card-success card-modulo-main elevation-1">
    <x-modulo-index-header
        titulo="Transportistas"
        icono="fa-id-card"
        :registros="$transportistas->total()"
        filtros-target="#filtrosTransportistasPanel"
        :nuevo-href="route('orgtrack.transportistas.create')"
        nuevo-text="Nuevo transportista"
        nuevo-can="transportistas.create"
    />

    <div id="filtrosTransportistasPanel" class="filtros-panel collapse {{ request()->hasAny(['q','activo']) || request('filtros_abiertos') === '1' ? 'show' : '' }}">
        <form method="GET" action="{{ route('orgtrack.transportistas.index') }}">
            <div class="row align-items-end">
                <div class="col-lg-5 col-md-6 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" class="form-control" placeholder="Nombre, email, usuario..."
                            value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Estado</label>
                    <select name="activo" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
            </div>
            <x-filtros-form-actions :limpiar-url="route('orgtrack.transportistas.index', ['filtros_abiertos' => 1])" />
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table x-table mb-0">
            <thead>
                <tr>
                    <th style="width:48px;">#</th>
                    <th>Transportista</th>
                    <th>Contacto</th>
                    <th>Usuario</th>
                    <th style="width:110px;">Estado</th>
                    <th style="width:80px;">Registro</th>
                    <th style="width:120px;" class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transportistas as $t)
                <tr>
                    <td style="color:#adb5bd;font-size:.8rem;">{{ $t->usuarioid }}</td>
                    <td>
                        <div class="d-flex align-items-center" style="gap:.65rem;">
                            <div class="avatar-circle">
                                {{ strtoupper(substr($t->nombre ?? 'T', 0, 1)) }}{{ strtoupper(substr($t->apellido ?? '', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;color:#1a252f;">{{ $t->nombre }} {{ $t->apellido }}</div>
                                @if($t->informacionadicional)
                                    <small class="text-muted">{{ Str::limit($t->informacionadicional, 40) }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($t->email)
                            <div><i class="fas fa-envelope fa-xs text-muted mr-1"></i><span style="font-size:.85rem;">{{ $t->email }}</span></div>
                        @endif
                        @if($t->telefono)
                            <div><i class="fas fa-phone fa-xs text-muted mr-1"></i><span style="font-size:.85rem;">{{ $t->telefono }}</span></div>
                        @endif
                        @if(!$t->email && !$t->telefono)
                            <span class="text-muted" style="font-size:.8rem;">Sin contacto</span>
                        @endif
                    </td>
                    <td>
                        @if($t->nombreusuario)
                            <code style="font-size:.8rem;background:#f2f7f3;padding:.15em .45em;border-radius:4px;color:#2c5530;">{{ $t->nombreusuario }}</code>
                        @else
                            <span class="text-muted" style="font-size:.8rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($t->activo)
                            <span class="badge-activo"><i class="fas fa-circle fa-xs mr-1"></i>Activo</span>
                        @else
                            <span class="badge-inactivo"><i class="fas fa-circle fa-xs mr-1"></i>Inactivo</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem;color:#adb5bd;">
                        {{ optional($t->fecharegistro)->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-right">
                        @can('transportistas.update')
                        <a href="{{ route('orgtrack.transportistas.edit', $t->usuarioid) }}"
                           class="btn btn-sm btn-outline-primary btn-accion" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                        @can('transportistas.delete')
                        <form action="{{ route('orgtrack.transportistas.destroy', $t->usuarioid) }}"
                              method="POST" class="d-inline-block"
                              onsubmit="return confirm('¿Eliminar al transportista {{ addslashes($t->nombre) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-truck-moving d-block"></i>
                            <p class="mb-1" style="font-size:1rem;font-weight:600;">No hay transportistas registrados</p>
                            <small>
                                @if(request('q'))
                                    No se encontraron resultados para <strong>"{{ request('q') }}"</strong>.
                                @else
                                    Crea el primer transportista con el botón de arriba.
                                @endif
                            </small>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transportistas->hasPages())
    <div class="card-footer" style="background:#fafbfc;border-top:1px solid #f0f0f0;border-radius:0 0 14px 14px;">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Mostrando {{ $transportistas->firstItem() }}–{{ $transportistas->lastItem() }} de {{ $transportistas->total() }} registros
            </small>
            {{ $transportistas->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
