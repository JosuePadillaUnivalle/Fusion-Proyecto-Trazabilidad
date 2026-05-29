@extends('layouts.app')

@section('title', 'Transportistas | AgroNexus')
@section('page_title', 'Transportistas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Transportistas</li>
@endsection

@push('styles')
<style>
.env-stat{border-radius:14px;padding:20px 22px;color:#fff;position:relative;overflow:hidden;transition:transform .18s;margin-bottom:1.2rem;}
.env-stat:hover{transform:translateY(-3px);}
.env-stat .s-icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:2.2rem;opacity:.22;}
.env-stat .s-num{font-size:2rem;font-weight:800;line-height:1;margin-bottom:4px;}
.env-stat .s-lbl{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;opacity:.88;}
.env-stat.total{background:linear-gradient(135deg,#065f46,#059669);}
.x-card { border:0; border-radius:14px; box-shadow:0 6px 20px rgba(18,38,63,.08); }
.x-card .card-header { background:#fff; border-bottom:1px solid #f0f0f0; border-radius:14px 14px 0 0 !important; padding:.9rem 1.2rem; }
.x-table thead th { background:#f2f7f3; border-bottom:0; font-size:.77rem; text-transform:uppercase; letter-spacing:.04em; color:#4a7c59; font-weight:700; padding:.65rem 1rem; }
.x-table tbody td { vertical-align:middle; padding:.7rem 1rem; }
.x-table tbody tr:hover { background:#f8fdf9; }
.avatar-circle { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,#fd7e14,#ffc107); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; flex-shrink:0; }
.badge-activo { background:#e8f5e8; color:#2c5530; font-size:.73rem; padding:.25em .65em; border-radius:20px; font-weight:600; }
.badge-inactivo { background:#fdecea; color:#c62828; font-size:.73rem; padding:.25em .65em; border-radius:20px; font-weight:600; }
.badge-nd { background:#f0f0f0; color:#888; font-size:.73rem; padding:.25em .65em; border-radius:20px; font-weight:600; }
.search-bar { border-radius:8px 0 0 8px !important; border-right:0 !important; }
.search-btn { border-radius:0 8px 8px 0 !important; background:#2c5530; border-color:#2c5530; color:#fff; }
.empty-state { padding:3rem; text-align:center; color:#adb5bd; }
.empty-state i { font-size:2.5rem; display:block; margin-bottom:.6rem; }
.loading-row td { text-align:center; color:#adb5bd; padding:2rem; }
.spinner-sm { width:1rem; height:1rem; border:.15em solid currentColor; border-right-color:transparent; border-radius:50%; animation:spinner .65s linear infinite; display:inline-block; vertical-align:middle; margin-right:.4rem; }
@keyframes spinner { to { transform:rotate(360deg); } }
</style>
@endpush

@section('content')

<div class="row mb-3">
    <div class="col-md-4">
        <div class="env-stat total">
            <div class="s-num" id="stat-transportistas">—</div>
            <div class="s-lbl">Transportistas registrados</div>
            <div class="s-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Equipo de transporte registrado en el sistema.</p>
    @can('transportistas.create')
    <a href="{{ route('orgtrack.transportistas.create') }}" class="btn btn-sm btn-success" style="border-radius:8px;">
        <i class="fas fa-plus mr-1"></i> Nuevo transportista
    </a>
    @endcan
</div>

<div id="aviso-demo-local" class="alert alert-info d-none mb-3" role="alert"></div>

<div class="card x-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h3 class="card-title mb-0" style="font-weight:700;color:#2c5530;">
                <i class="fas fa-id-card mr-2"></i>Directorio de transportistas
            </h3>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem;">
            <div class="input-group" style="max-width:260px;">
                <input type="text" id="search-input" class="form-control search-bar" placeholder="Filtrar por nombre...">
                <div class="input-group-append">
                    <button class="btn search-btn" onclick="filtrarTabla()"><i class="fas fa-search"></i></button>
                </div>
            </div>
            @can('transportistas.view')
            <a href="{{ route('orgtrack.transportistas.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;" title="Gestión completa">
                <i class="fas fa-cog"></i>
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table x-table mb-0" id="tabla-transportistas-el">
            <thead>
                <tr>
                    <th style="width:44px;">#</th>
                    <th>Transportista</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tabla-transportistas">
                <tr class="loading-row">
                    <td colspan="5">
                        <span class="spinner-sm"></span>Cargando transportistas...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" style="background:#fafbfc;border-top:1px solid #f0f0f0;border-radius:0 0 14px 14px;">
        <small class="text-muted" id="contador-resultados">—</small>
    </div>
</div>
@endsection

@push('scripts')
<script>
let todosLosTransportistas = [];

const tbody = document.getElementById('tabla-transportistas');
const contadorEl = document.getElementById('contador-resultados');
const searchInput = document.getElementById('search-input');

fetch("{{ route('envios.api.transportistas') }}")
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        const meta = data._meta || {};
        const aviso = document.getElementById('aviso-demo-local');
        if (meta.fuente === 'fusion_local' && meta.mensaje) {
            aviso.innerHTML = '<i class="fas fa-info-circle mr-1"></i>' + meta.mensaje;
            aviso.classList.remove('d-none');
        }

        const rows = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
        todosLosTransportistas = rows;
        document.getElementById('stat-transportistas').textContent = rows.length;
        renderizarFilas(rows);
    })
    .catch(err => {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">
            <i class="fas fa-exclamation-triangle mr-1"></i>No se pudo cargar la información de transportistas.
            <br><small class="text-muted">${err.message}</small>
        </td></tr>`;
        contadorEl.textContent = 'Error al cargar datos';
    });

function renderizarFilas(rows) {
    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state">
            <i class="fas fa-truck-moving"></i>
            <div style="font-size:.95rem;font-weight:600;">Sin transportistas registrados</div>
            <small>Aún no hay transportistas en el sistema.</small>
        </div></td></tr>`;
        contadorEl.textContent = '0 transportistas';
        return;
    }

    tbody.innerHTML = rows.map((t, i) => {
        const persona = t.persona || {};
        const nombre = [persona.nombre ?? t.nombre, persona.apellido ?? t.apellido].filter(Boolean).join(' ') || 'N/D';
        const iniciales = nombre.split(' ').slice(0,2).map(w => w[0]?.toUpperCase() ?? '').join('');
        const correo = t.usuario?.correo ?? t.email ?? t.correo ?? '';
        const telefono = t.telefono ?? '';
        const estado = t.estado?.nombre ?? t.estadotransportista?.nombre ?? t.estado ?? null;
        const activo = t.activo ?? (estado === 'activo' || estado === 'disponible' || estado == null ? null : true);

        const estadoHtml = activo === false
            ? '<span class="badge-inactivo"><i class="fas fa-circle fa-xs mr-1"></i>Inactivo</span>'
            : estado
                ? `<span class="badge-activo"><i class="fas fa-circle fa-xs mr-1"></i>${estado}</span>`
                : '<span class="badge-nd">N/D</span>';

        return `<tr data-nombre="${nombre.toLowerCase()}">
            <td style="color:#adb5bd;font-size:.78rem;">${i + 1}</td>
            <td>
                <div class="d-flex align-items-center" style="gap:.6rem;">
                    <div class="avatar-circle">${iniciales || 'T'}</div>
                    <span style="font-weight:600;">${nombre}</span>
                </div>
            </td>
            <td style="font-size:.85rem;">${correo ? `<i class="fas fa-envelope fa-xs text-muted mr-1"></i>${correo}` : '<span class="text-muted">—</span>'}</td>
            <td style="font-size:.85rem;">${telefono ? `<i class="fas fa-phone fa-xs text-muted mr-1"></i>${telefono}` : '<span class="text-muted">—</span>'}</td>
            <td>${estadoHtml}</td>
        </tr>`;
    }).join('');

    contadorEl.textContent = `${rows.length} transportista${rows.length !== 1 ? 's' : ''} encontrado${rows.length !== 1 ? 's' : ''}`;
}

function filtrarTabla() {
    const q = (searchInput.value || '').toLowerCase().trim();
    if (!q) {
        renderizarFilas(todosLosTransportistas);
        return;
    }
    const filtrados = todosLosTransportistas.filter(t => {
        const persona = t.persona || {};
        const nombre = [persona.nombre ?? t.nombre, persona.apellido ?? t.apellido].filter(Boolean).join(' ').toLowerCase();
        const correo = (t.usuario?.correo ?? t.email ?? t.correo ?? '').toLowerCase();
        return nombre.includes(q) || correo.includes(q);
    });
    renderizarFilas(filtrados);
}

searchInput.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') filtrarTabla();
    else if (this.value === '') renderizarFilas(todosLosTransportistas);
});
</script>
@endpush
