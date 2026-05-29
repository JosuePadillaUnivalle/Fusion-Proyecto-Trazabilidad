@extends('layouts.app')

@section('title', 'Direcciones | AgroNexus')
@section('page_title', 'Direcciones de envíos')

@push('styles')
<style>
.env-stat{border-radius:14px;padding:20px 22px;color:#fff;position:relative;overflow:hidden;transition:transform .18s;}
.env-stat:hover{transform:translateY(-3px);}
.env-stat .s-icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:2.2rem;opacity:.22;}
.env-stat .s-num{font-size:2rem;font-weight:800;line-height:1;margin-bottom:4px;}
.env-stat .s-lbl{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;opacity:.88;}
.env-stat.total  {background:linear-gradient(135deg,#065f46,#059669);}
.env-stat.origen {background:linear-gradient(135deg,#1d4ed8,#3b82f6);}
.env-stat.destino{background:linear-gradient(135deg,#0e7490,#06b6d4);}
.tipo-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:.72rem;font-weight:700;}
.tipo-origen {background:#dbeafe;color:#1e40af;}
.tipo-destino{background:#d1fae5;color:#065f46;}
</style>
@endpush

@section('content')

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="env-stat total">
            <div class="s-num" id="stat-total">—</div>
            <div class="s-lbl">Puntos logísticos</div>
            <div class="s-icon"><i class="fas fa-map-marked-alt"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="env-stat origen">
            <div class="s-num" id="stat-origenes">—</div>
            <div class="s-lbl">Orígenes</div>
            <div class="s-icon"><i class="fas fa-arrow-up"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="env-stat destino">
            <div class="s-num" id="stat-destinos">—</div>
            <div class="s-lbl">Destinos</div>
            <div class="s-icon"><i class="fas fa-arrow-down"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div><i class="fas fa-map-pin text-success mr-2"></i><strong>Direcciones de origen y destino</strong></div>
        <small class="text-muted" id="contador-dirs">Mostrando 0 registro(s)</small>
    </div>
    <div class="card-body" style="border-bottom:1px solid #e2e8f0;padding:14px 18px;">
        <div class="row align-items-end">
            <div class="col-md-6 mb-2">
                <label class="form-label" style="font-size:.75rem;">Buscar en dirección</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                    <input type="text" id="buscar-dir" class="form-control" placeholder="Ciudad, zona, almacén, planta…">
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label" style="font-size:.75rem;">Tipo de punto</label>
                <select id="filtro-tipo" class="form-control form-control-sm">
                    <option value="">Origen y destino</option>
                    <option value="Origen">Solo orígenes</option>
                    <option value="Destino">Solo destinos</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label d-none d-md-block" style="font-size:.75rem;">&nbsp;</label>
                <button class="btn btn-sm btn-outline-secondary btn-block" id="limpiar-filtros">
                    <i class="fas fa-times mr-1"></i>Limpiar filtros
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th style="width:50px;">#</th><th style="width:130px;">Tipo</th><th>Dirección</th></tr></thead>
                <tbody id="tabla-direcciones">
                    <tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando…</td></tr>
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
    function render(dirs){
        document.getElementById('contador-dirs').textContent='Mostrando '+dirs.length+' registro(s)';
        const tbody=document.getElementById('tabla-direcciones');
        if(!dirs.length){tbody.innerHTML='<tr><td colspan="3" class="text-center text-muted py-5"><i class="fas fa-map fa-2x mb-2 d-block"></i>No hay direcciones derivadas de envíos registrados.</td></tr>';return;}
        tbody.innerHTML=dirs.map((d,i)=>`<tr><td class="text-muted">${i+1}</td><td><span class="tipo-badge tipo-${d.tipo.toLowerCase()}"><i class="fas ${d.tipo==='Origen'?'fa-arrow-up':'fa-arrow-down'} mr-1"></i>${d.tipo}</span></td><td style="font-size:.85rem;">${d.valor}</td></tr>`).join('');
    }
    function filter(){
        const q=document.getElementById('buscar-dir').value.toLowerCase();
        const t=document.getElementById('filtro-tipo').value;
        render(all.filter(d=>(!q||d.valor.toLowerCase().includes(q))&&(!t||d.tipo===t)));
    }
    fetch("{{ route('envios.api.envios') }}").then(r=>r.json()).then(data=>{
        const meta=data._meta||{};
        if(meta.fuente==='fusion_local'){const a=document.getElementById('aviso-demo-local');a.textContent=meta.mensaje||'';a.classList.remove('d-none');}
        const envios=Array.isArray(data?.data)?data.data:(Array.isArray(data)?data:[]);
        const seen=new Set();
        envios.forEach(e=>{
            [['Origen',['direccion_origen','origen_direccion','origen']],['Destino',['direccion_destino','destino_direccion','destino']]].forEach(([tipo,keys])=>{
                const valor=keys.map(k=>(e[k]||'').toString().trim()).find(v=>v!=='')||'';
                if(!valor)return;const key=tipo+'|'+valor;
                if(!seen.has(key)){seen.add(key);all.push({tipo,valor});}
            });
        });
        document.getElementById('stat-total').textContent=all.length;
        document.getElementById('stat-origenes').textContent=all.filter(d=>d.tipo==='Origen').length;
        document.getElementById('stat-destinos').textContent=all.filter(d=>d.tipo==='Destino').length;
        render(all);
    }).catch(()=>{document.getElementById('tabla-direcciones').innerHTML='<tr><td colspan="3" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle mr-2"></i>No se pudo cargar la información.</td></tr>';});
    document.getElementById('buscar-dir').addEventListener('input',filter);
    document.getElementById('filtro-tipo').addEventListener('change',filter);
    document.getElementById('limpiar-filtros').addEventListener('click',()=>{document.getElementById('buscar-dir').value='';document.getElementById('filtro-tipo').value='';filter();});
})();
</script>
@endpush
