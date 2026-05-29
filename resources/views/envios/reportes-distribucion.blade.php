@extends('layouts.app')

@section('title', 'Reportes de distribución | AgroNexus')
@section('page_title', 'Reportes de distribución')

@push('styles')
<style>
.rd-stat{border-radius:14px;padding:18px 20px;color:#fff;position:relative;overflow:hidden;transition:transform .18s;margin-bottom:.8rem;}
.rd-stat:hover{transform:translateY(-3px);}
.rd-stat .s-icon{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:2rem;opacity:.22;}
.rd-stat .s-num{font-size:1.8rem;font-weight:800;line-height:1;margin-bottom:3px;}
.rd-stat .s-lbl{font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;opacity:.88;}
.rd-stat.total    {background:linear-gradient(135deg,#065f46,#059669);}
.rd-stat.pendiente{background:linear-gradient(135deg,#78350f,#d97706);}
.rd-stat.asignado {background:linear-gradient(135deg,#1d4ed8,#3b82f6);}
.rd-stat.en-ruta  {background:linear-gradient(135deg,#0e7490,#06b6d4);}
.rd-stat.entregado{background:linear-gradient(135deg,#5b21b6,#8b5cf6);}
.rd-stat.stock    {background:linear-gradient(135deg,#92400e,#f59e0b);}
.rd-stat.lineas   {background:linear-gradient(135deg,#1e293b,#475569);}
.buscar-wrap{padding:12px 16px;border-bottom:1px solid #e2e8f0;}
</style>
@endpush

@section('content')

<div class="card mb-4" style="border-left:4px solid #10b981;">
    <div class="card-body py-2 px-3">
        <small><i class="fas fa-info-circle text-success mr-1"></i>
        <strong>Reportes de distribución</strong> — Filtrá cada tabla y hacé clic en una fila para ver los envíos o ir al detalle.</small>
    </div>
</div>

{{-- 7 Stat boxes --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-2"><div class="rd-stat total"><div class="s-num" id="st-total">—</div><div class="s-lbl">Asignaciones totales</div><div class="s-icon"><i class="fas fa-clipboard-list"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="rd-stat pendiente"><div class="s-num" id="st-pendiente">—</div><div class="s-lbl">Pendientes</div><div class="s-icon"><i class="fas fa-clock"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="rd-stat asignado"><div class="s-num" id="st-asignado">—</div><div class="s-lbl">Asignados</div><div class="s-icon"><i class="fas fa-user-check"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="rd-stat en-ruta"><div class="s-num" id="st-ruta">—</div><div class="s-lbl">En ruta</div><div class="s-icon"><i class="fas fa-map-marker-alt"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="rd-stat entregado"><div class="s-num" id="st-entregado">—</div><div class="s-lbl">Entregados</div><div class="s-icon"><i class="fas fa-check-double"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="rd-stat stock"><div class="s-num" id="st-stock">{{ $counts['stock_bodegas'] ?? 0 }}</div><div class="s-lbl">Stock en bodegas</div><div class="s-icon"><i class="fas fa-warehouse"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="rd-stat lineas"><div class="s-num" id="st-lineas">{{ $counts['lineas_inventario'] ?? 0 }}</div><div class="s-lbl">Líneas inventario</div><div class="s-icon"><i class="fas fa-list-ol"></i></div></div></div>
</div>

<div id="aviso-demo-local" class="alert alert-info d-none mb-3"></div>

<div class="row">
    {{-- Top transportistas --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><i class="fas fa-trophy text-warning mr-2"></i><strong>Top transportistas</strong></div>
                <small class="text-muted" id="cnt-transportistas">0 registro(s)</small>
            </div>
            <div class="buscar-wrap">
                <div class="input-group input-group-sm" style="max-width:320px;">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                    <input type="text" id="buscar-transp" class="form-control" placeholder="Nombre del transportista…">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Transportista</th><th class="text-right">Asignaciones</th></tr></thead>
                        <tbody id="tbody-transportistas"><tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando…</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Envíos por estado --}}
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><i class="fas fa-chart-bar text-success mr-2"></i><strong>Envíos por estado</strong></div>
                <small class="text-muted" id="cnt-estados">0 registro(s)</small>
            </div>
            <div class="buscar-wrap">
                <input type="text" id="buscar-estado" class="form-control form-control-sm" placeholder="Buscar estado…">
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Estado</th><th class="text-right">Cantidad</th></tr></thead>
                        <tbody id="tbody-estados"><tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando…</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Envíos por destino --}}
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><i class="fas fa-map-pin text-success mr-2"></i><strong>Envíos por destino</strong></div>
                <small class="text-muted" id="cnt-destinos">0 registro(s)</small>
            </div>
            <div class="buscar-wrap">
                <input type="text" id="buscar-destino" class="form-control form-control-sm" placeholder="Buscar destino…">
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Destino</th><th class="text-right">Cantidad</th></tr></thead>
                        <tbody id="tbody-destinos"><tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando…</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
    const toArr=d=>Array.isArray(d?.data)?d.data:(Array.isArray(d)?d:[]);
    const countBy=(items,fn)=>{const m={};items.forEach(i=>{const k=(fn(i)||'Sin dato').toString().trim()||'Sin dato';m[k]=(m[k]||0)+1;});return Object.entries(m).sort((a,b)=>b[1]-a[1]);};
    let allEnvios=[], allTransp=[];

    // Transportistas from local DB
    @php
        $topT = \App\Models\EnvioAsignacionMultiple::selectRaw('transportista_usuarioid, count(*) as c')
            ->groupBy('transportista_usuarioid')->orderByDesc('c')->limit(10)->get();
    @endphp
    allTransp = @json($topT->map(function($t){ $u = \App\Models\Usuario::find($t->transportista_usuarioid); return ['nombre' => $u ? trim(($u->nombre??'').(' '.($u->apellido??''))) : 'N/A', 'c' => $t->c]; }));
    renderTransp(allTransp);

    function renderTransp(rows){
        document.getElementById('cnt-transportistas').textContent=rows.length+' registro(s)';
        const tb=document.getElementById('tbody-transportistas');
        if(!rows.length){tb.innerHTML='<tr><td colspan="2" class="text-center text-muted py-4">Sin datos de transportistas.</td></tr>';return;}
        tb.innerHTML=rows.map(t=>`<tr><td>${t.nombre}</td><td class="text-right font-weight-600">${t.c}</td></tr>`).join('');
    }
    document.getElementById('buscar-transp').addEventListener('input',function(){
        const q=this.value.toLowerCase();
        renderTransp(allTransp.filter(t=>t.nombre.toLowerCase().includes(q)));
    });

    // Envíos from API
    fetch("{{ route('envios.api.envios') }}").then(r=>r.json()).then(data=>{
        const meta=data._meta||{};
        if(meta.fuente==='fusion_local'){const a=document.getElementById('aviso-demo-local');a.textContent=meta.mensaje||'';a.classList.remove('d-none');}
        allEnvios=toArr(data);

        // Stats
        const byEstado=e=>(e.estado||e.estado_actual||e.nombre_estado||'').toLowerCase();
        document.getElementById('st-total').textContent=allEnvios.length;
        document.getElementById('st-pendiente').textContent=allEnvios.filter(e=>byEstado(e).includes('pendiente')).length;
        document.getElementById('st-asignado').textContent=allEnvios.filter(e=>byEstado(e).includes('asignado')).length;
        document.getElementById('st-ruta').textContent=allEnvios.filter(e=>byEstado(e).includes('ruta')||byEstado(e).includes('tránsito')||byEstado(e).includes('transito')).length;
        document.getElementById('st-entregado').textContent=allEnvios.filter(e=>byEstado(e).includes('entregado')).length;

        // Tables
        const estados=countBy(allEnvios,e=>e.estado||e.estado_actual||e.nombre_estado);
        const destinos=countBy(allEnvios,e=>e.destino||e.direccion_destino||e.destino_direccion);
        renderTable('tbody-estados','cnt-estados',estados,'Buscar estado…','No hay datos de estado.');
        renderTable('tbody-destinos','cnt-destinos',destinos,'Buscar destino…','No hay datos de destino.');

        // Filters
        document.getElementById('buscar-estado').addEventListener('input',function(){renderTable('tbody-estados','cnt-estados',estados.filter(([k])=>k.toLowerCase().includes(this.value.toLowerCase())),'','No hay datos.');});
        document.getElementById('buscar-destino').addEventListener('input',function(){renderTable('tbody-destinos','cnt-destinos',destinos.filter(([k])=>k.toLowerCase().includes(this.value.toLowerCase())),'','No hay datos.');});
    }).catch(()=>{
        document.getElementById('st-total').textContent='0';
        document.getElementById('st-pendiente').textContent='0';
        document.getElementById('st-asignado').textContent='0';
        document.getElementById('st-ruta').textContent='0';
        document.getElementById('st-entregado').textContent='0';
        ['tbody-estados','tbody-destinos'].forEach(id=>{document.getElementById(id).innerHTML='<tr><td colspan="2" class="text-center text-danger py-3"><i class="fas fa-exclamation-circle mr-1"></i>No se pudo cargar.</td></tr>';});
    });

    function renderTable(tbodyId,cntId,entries,placeholder,empty){
        document.getElementById(cntId).textContent=entries.length+' registro(s)';
        const tb=document.getElementById(tbodyId);
        if(!entries.length){tb.innerHTML=`<tr><td colspan="2" class="text-center text-muted py-4">${empty}</td></tr>`;return;}
        tb.innerHTML=entries.map(([k,v])=>`<tr><td>${k}</td><td class="text-right font-weight-600">${v}</td></tr>`).join('');
    }
})();
</script>
@endpush
