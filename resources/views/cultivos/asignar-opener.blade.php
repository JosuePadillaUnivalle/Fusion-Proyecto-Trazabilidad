@extends('layouts.app')

@section('title', 'Cultivo creado | AgroFusion')
@section('page_title', 'Cultivo creado')

@section('content')
<div class="container-fluid py-5 text-center">
    <i class="fas fa-check-circle text-success mb-3" style="font-size:3rem;"></i>
    <h4 class="font-weight-bold">«{{ $cultivo->nombre }}» registrado</h4>
    <p class="text-muted mb-0" id="msgAsignar">Asignando al formulario de lote…</p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const cultivoid = {{ (int) $cultivo->cultivoid }};
    const nombre = @json($cultivo->nombre);
    const selectorId = @json($selectorId);
    const msg = document.getElementById('msgAsignar');

    if (window.opener && !window.opener.closed && window.opener.CatalogoSelector) {
        window.opener.CatalogoSelector.setValue(selectorId, cultivoid, nombre);
        window.close();
        return;
    }

    if (msg) {
        msg.textContent = 'Redirigiendo al formulario de lote…';
    }
    window.location.href = @json(route('lotes.create', ['cultivoid' => $cultivo->cultivoid]));
})();
</script>
@endpush
