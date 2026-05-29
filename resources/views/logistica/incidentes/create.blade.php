@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Reportar incidente</h3>

    @include('partials.flash-messages')

    <form method="POST" action="{{ route('logistica.incidentes.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Envío (externo_id)</label>
            <input name="externo_envio_id" class="form-control" placeholder="ID externo del envío">
        </div>

        <div class="mb-3">
            <label class="form-label">Pedido (opcional)</label>
            <input name="pedidoid" class="form-control" placeholder="ID pedido">
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo de incidente</label>
            <select name="tipo" id="tipo" class="form-control" required>
                <option value="Retraso">Retraso</option>
                <option value="Daño">Daño</option>
                <option value="Falta de productos">Falta de productos</option>
                <option value="Otro">Otro</option>
            </select>
        </div>

        <div class="mb-3 d-none" id="tipo-otro-div">
            <label class="form-label">Especificar tipo</label>
            <input name="tipo_otro" id="tipo_otro" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4" required></textarea>
        </div>

        <button class="btn btn-primary">Reportar</button>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('tipo').addEventListener('change', function(){
    const v = this.value;
    const div = document.getElementById('tipo-otro-div');
    if (v === 'Otro') div.classList.remove('d-none'); else div.classList.add('d-none');
});

// When submitting, if tipo is Otro, copy tipo_otro into tipo
document.querySelector('form').addEventListener('submit', function(e){
    const tipo = document.getElementById('tipo');
    if (tipo.value === 'Otro') {
        const other = document.getElementById('tipo_otro').value.trim();
        if (!other) { e.preventDefault(); alert('Especifique el tipo de incidente'); }
        // set hidden field
        tipo.value = other;
    }
});
</script>
@endpush

@endsection
@extends('layouts.app')
@push('styles')
<style>.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Nuevo incidente de envío</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card x-card">
            <div class="card-body">
                <form method="POST" action="{{ route('logistica.incidentes.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>ID de envío (opcional)</label>
                            <input name="externo_envio_id" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>ID pedido (opcional)</label>
                            <input type="number" name="pedidoid" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Tipo</label>
                            <input name="tipo" class="form-control" required value="logistico">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="5" required></textarea>
                    </div>
                    <button class="btn btn-primary">Registrar incidente</button>
                    <a href="{{ route('logistica.incidentes.index') }}" class="btn btn-default">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

