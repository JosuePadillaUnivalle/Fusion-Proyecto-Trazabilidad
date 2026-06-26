@extends('layouts.public-trazabilidad')

@section('title', $titulo.' | AgroFusion')

@push('styles')
<style>
.rcp-page { text-align: center; }
.rcp-brand { font-weight: 800; color: #166534; font-size: .9rem; margin-bottom: .35rem; }
.rcp-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: .25rem; }
.rcp-codigo {
    display: inline-block; background: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe;
    border-radius: 999px; padding: .25rem .75rem; font-size: .72rem; font-weight: 700; margin-bottom: 1rem;
}
.rcp-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    padding: 1.15rem; text-align: left; box-shadow: 0 4px 18px rgba(15,23,42,.06);
}
.rcp-label { font-size: .78rem; font-weight: 700; color: #475569; margin-bottom: .35rem; display: block; }
.rcp-input {
    width: 100%; border: 1px solid #cbd5e1; border-radius: 10px;
    padding: .7rem .85rem; font-size: .95rem; margin-bottom: 1rem;
}
.rcp-input:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
.rcp-firma-box {
    border: 2px dashed #cbd5e1; border-radius: 10px; background: #fff;
    touch-action: none; cursor: crosshair; width: 100%; height: 180px;
}
.rcp-actions { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .75rem; }
.rcp-btn {
    border: 0; border-radius: 10px; padding: .65rem 1rem; font-weight: 700; font-size: .88rem; cursor: pointer;
}
.rcp-btn--ghost { background: #f1f5f9; color: #334155; }
.rcp-btn--primary {
    background: linear-gradient(135deg, #5b21b6, #7c3aed); color: #fff;
    box-shadow: 0 3px 10px rgba(91,33,182,.28);
}
.rcp-alert {
    border-radius: 10px; padding: .85rem 1rem; font-size: .88rem; margin-bottom: 1rem;
}
.rcp-alert--ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
.rcp-alert--warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.rcp-alert--info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.rcp-modal-backdrop {
    position: fixed; inset: 0; background: rgba(15,23,42,.45);
    display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem;
}
.rcp-modal-backdrop[hidden] { display: none !important; }
.rcp-modal {
    background: #fff; border-radius: 14px; padding: 1.25rem 1.35rem; max-width: 320px; width: 100%;
    box-shadow: 0 12px 40px rgba(15,23,42,.18); text-align: center;
}
.rcp-modal h2 { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 .5rem; }
.rcp-modal p { font-size: .9rem; color: #475569; margin: 0 0 1rem; }
</style>
@endpush

@section('content')
<div class="rcp-page">
    <div class="rcp-brand"><i class="fas fa-truck-loading mr-1"></i> AgroFusion</div>
    <h1 class="rcp-title">{{ $titulo }}</h1>
    <span class="rcp-codigo">{{ $codigo }}</span>

    @if(session('exito'))
        <div class="rcp-alert rcp-alert--ok"><i class="fas fa-check-circle mr-1"></i> {{ session('exito') }}</div>
    @endif

    @if($errors->any())
        <div class="rcp-alert rcp-alert--warn">{{ $errors->first() }}</div>
    @endif

    <div class="rcp-card">
        @if($sinFirmaTransportista)
            <div class="rcp-alert rcp-alert--info mb-0">
                <i class="fas fa-hourglass-half mr-1"></i>
                El transportista aún no ha firmado en el sistema. Espere un momento e intente de nuevo.
            </div>
        @elseif($yaFirmado)
            <div class="rcp-alert rcp-alert--ok mb-0">
                <i class="fas fa-check-circle mr-1"></i>
                La recepción ya fue firmada. Puede cerrar esta página.
            </div>
        @else
            <p class="small text-muted mb-3">
                Indique su nombre y firme para confirmar la recepción de la carga.
            </p>
            <form method="POST" action="{{ route('recepcion.publica.firmar', $token) }}" id="form-recepcion-publica">
                @csrf
                <label class="rcp-label" for="nombrefirmante">Nombre completo del receptor</label>
                <input type="text" class="rcp-input" id="nombrefirmante" name="nombrefirmante"
                       value="{{ old('nombrefirmante') }}" placeholder="Ej: María López" autocomplete="name">

                <label class="rcp-label">Firma</label>
                <canvas class="rcp-firma-box" data-firma-canvas="recepcion" width="400" height="180"></canvas>
                <input type="hidden" name="imagen_firma" id="imagen_firma">

                <div class="rcp-actions">
                    <button type="button" class="rcp-btn rcp-btn--ghost btn-limpiar-firma" data-target="recepcion">Limpiar</button>
                    <button type="submit" class="rcp-btn rcp-btn--primary" id="btn-enviar-firma">
                        <i class="fas fa-file-signature mr-1"></i> Confirmar recepción
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<div class="rcp-modal-backdrop" id="modal-nombre-requerido" hidden>
    <div class="rcp-modal" role="dialog" aria-modal="true" aria-labelledby="modal-nombre-titulo">
        <h2 id="modal-nombre-titulo">Nombre requerido</h2>
        <p>Debe escribir su nombre antes de confirmar la recepción.</p>
        <button type="button" class="rcp-btn rcp-btn--primary" id="btn-cerrar-modal-nombre">Entendido</button>
    </div>
</div>
@endsection

@push('scripts')
@if(! $yaFirmado && ! $sinFirmaTransportista)
<script src="{{ asset('js/firma-canvas.js') }}?v=3"></script>
<script>
(function () {
    const form = document.getElementById('form-recepcion-publica');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const nombre = document.getElementById('nombrefirmante').value.trim();
        if (!nombre) {
            document.getElementById('modal-nombre-requerido').hidden = false;
            return;
        }

        const canvas = document.querySelector('[data-firma-canvas="recepcion"]');
        if (!canvas) return;

        let imagen = '';
        if (window.AgroFusionFirmaPads && window.AgroFusionFirmaPads.get) {
            const pad = window.AgroFusionFirmaPads.get('recepcion');
            if (pad) imagen = pad.toDataUrl();
        }

        if (!imagen || imagen.length < 100) {
            alert('Dibuje su firma antes de confirmar.');
            return;
        }

        document.getElementById('imagen_firma').value = imagen;
        const btn = document.getElementById('btn-enviar-firma');
        btn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('[name=_token]')?.value,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                nombrefirmante: nombre,
                imagen_firma: imagen,
            }),
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (res.ok) {
                    window.location.reload();
                } else {
                    alert(res.j.mensaje || 'No se pudo guardar la firma.');
                    btn.disabled = false;
                }
            })
            .catch(function () {
                alert('No se pudo conectar con el servidor. Intente de nuevo en unos segundos.');
                btn.disabled = false;
            });
    });

    const modalNombre = document.getElementById('modal-nombre-requerido');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal-nombre');
    if (modalNombre && btnCerrarModal) {
        btnCerrarModal.addEventListener('click', function () {
            modalNombre.hidden = true;
            document.getElementById('nombrefirmante')?.focus();
        });
        modalNombre.addEventListener('click', function (ev) {
            if (ev.target === modalNombre) {
                modalNombre.hidden = true;
            }
        });
    }
})();
</script>
@endif
@endpush
