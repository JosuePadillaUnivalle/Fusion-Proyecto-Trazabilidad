@extends('layouts.app')

@section('title', 'Vehículos | AgroNexus')
@section('page_title', 'Vehículos')

@push('styles')
<style>
.env-stat{border-radius:14px;padding:20px 22px;color:#fff;position:relative;overflow:hidden;transition:transform .18s;}
.env-stat:hover{transform:translateY(-3px);}
.env-stat .s-icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:2.2rem;opacity:.22;}
.env-stat .s-num{font-size:2rem;font-weight:800;line-height:1;margin-bottom:4px;}
.env-stat .s-lbl{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;opacity:.88;}
.env-stat.flota   {background:linear-gradient(135deg,#78350f,#d97706);}
.env-stat.activos {background:linear-gradient(135deg,#065f46,#059669);}
.env-stat.inactivos{background:linear-gradient(135deg,#7f1d1d,#dc2626);}
.v-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:.72rem;font-weight:700;}
.v-activo  {background:#d1fae5;color:#065f46;}
.v-inactivo{background:#fee2e2;color:#b91c1c;}
.v-tipo    {background:#e0f2fe;color:#0369a1;}
</style>
@endpush

@section('content')

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="env-stat flota">
            <div class="s-num" id="stat-flota">—</div>
            <div class="s-lbl">Vehículos en flota</div>
            <div class="s-icon"><i class="fas fa-truck"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="env-stat activos">
            <div class="s-num" id="stat-activos">—</div>
            <div class="s-lbl">Disponibles</div>
            <div class="s-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="env-stat inactivos">
            <div class="s-num" id="stat-inactivos">—</div>
            <div class="s-lbl">No disponibles</div>
            <div class="s-icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div><i class="fas fa-truck text-success mr-2"></i><strong>Flota logística</strong></div>
        <small class="text-muted" id="contador-veh">Mostrando 0 registro(s)</small>
    </div>
    <div class="card-body" style="border-bottom:1px solid #e2e8f0;padding:14px 18px;">
        <div class="row align-items-end">
            <div class="col-md-4 mb-2">
                <label class="form-label" style="font-size:.75rem;">Buscar</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                    <input type="text" id="buscar-veh" class="form-control" placeholder="Placa, tipo, estado…">
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label" style="font-size:.75rem;">Tipo de vehículo</label>
                <select id="filtro-tipo-veh" class="form-control form-control-sm"><option value="">Todos los tipos</option></select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label" style="font-size:.75rem;">Estado</label>
                <select id="filtro-estado-veh" class="form-control form-control-sm"><option value="">Todos los estados</option></select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label d-none d-md-block" style="font-size:.75rem;">&nbsp;</label>
                <button class="btn btn-sm btn-outline-secondary btn-block" id="limpiar-veh">
                    <i class="fas fa-times mr-1"></i>Limpiar filtros
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th style="width:50px;">#</th><th>Placa</th><th>Tipo</th><th>Estado</th><th>Capacidad</th></tr></thead>
                <tbody id="tabla-vehiculos">
                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="aviso-demo-local" class="alert alert-info d-none mt-3"></div>
@endsection

@push('scripts')
<script>
(function(){
    let all=[];
    function render(rows){
        document.getElementById('contador-veh').textContent='Mostrando '+rows.length+' registro(s)';
        const tbody=document.getElementById('tabla-vehiculos');
        if(!rows.length){tbody.innerHTML='<tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-truck fa-2x mb-2 d-block"></i>No hay vehículos registrados en la operación.</td></tr>';return;}
        tbody.innerHTML=rows.map((v,i)=>{
            const tipo=v.tipo_vehiculo?.nombre||v.tipoVehiculo?.nombre||v.tipo||'N/D';
            const estado=v.estado_vehiculo?.nombre||v.estadoVehiculo?.nombre||v.estado||'N/D';
            const cap=v.capacidad_carga||v.capacidad||'—';
            const activo=estado.toLowerCase().includes('activ')||estado.toLowerCase().includes('disponib');
            return `<tr><td class="text-muted">${i+1}</td><td><strong>${v.placa||'N/D'}</strong></td><td><span class="v-badge v-tipo">${tipo}</span></td><td><span class="v-badge ${activo?'v-activo':'v-inactivo'}">${estado}</span></td><td>${cap}</td></tr>`;
        }).join('');
    }
    function filter(){
        const q=document.getElementById('buscar-veh').value.toLowerCase();
        const t=document.getElementById('filtro-tipo-veh').value;
        const e=document.getElementById('filtro-estado-veh').value;
        render(all.filter(v=>{
            const tipo=v.tipo_vehiculo?.nombre||v.tipo||'';
            const estado=v.estado_vehiculo?.nombre||v.estado||'';
            return(!q||[v.placa||'',tipo,estado].join(' ').toLowerCase().includes(q))&&(!t||tipo===t)&&(!e||estado===e);
        }));
    }
    fetch("{{ route('envios.api.vehiculos') }}").then(r=>r.json()).then(data=>{
        const meta=data._meta||{};
        if(meta.fuente==='fusion_local'){const a=document.getElementById('aviso-demo-local');a.textContent=meta.mensaje||'';a.classList.remove('d-none');}
        all=Array.isArray(data?.data)?data.data:(Array.isArray(data)?data:[]);
        document.getElementById('stat-flota').textContent=all.length;
        const activos=all.filter(v=>{const e=(v.estado_vehiculo?.nombre||v.estado||'').toLowerCase();return e.includes('activ')||e.includes('disponib');}).length;
        document.getElementById('stat-activos').textContent=activos;
        document.getElementById('stat-inactivos').textContent=all.length-activos;
        const tipos=[...new Set(all.map(v=>v.tipo_vehiculo?.nombre||v.tipo||'').filter(Boolean))];
        const estados=[...new Set(all.map(v=>v.estado_vehiculo?.nombre||v.estado||'').filter(Boolean))];
        const ts=document.getElementById('filtro-tipo-veh');
        tipos.forEach(t=>{const o=document.createElement('option');o.value=t;o.textContent=t;ts.appendChild(o);});
        const es=document.getElementById('filtro-estado-veh');
        estados.forEach(e=>{const o=document.createElement('option');o.value=e;o.textContent=e;es.appendChild(o);});
        render(all);
    }).catch(()=>{document.getElementById('tabla-vehiculos').innerHTML='<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle mr-2"></i>No se pudo cargar la información.</td></tr>';});
    document.getElementById('buscar-veh').addEventListener('input',filter);
    document.getElementById('filtro-tipo-veh').addEventListener('change',filter);
    document.getElementById('filtro-estado-veh').addEventListener('change',filter);
    document.getElementById('limpiar-veh').addEventListener('click',()=>{document.getElementById('buscar-veh').value='';document.getElementById('filtro-tipo-veh').value='';document.getElementById('filtro-estado-veh').value='';filter();});
})();
</script>
@endpush
