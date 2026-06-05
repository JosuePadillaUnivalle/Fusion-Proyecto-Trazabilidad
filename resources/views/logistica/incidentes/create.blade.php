@extends('layouts.app')

@section('title', 'Nuevo incidente | AgroFusion')
@section('page_title', 'Nuevo incidente de envío')

@push('styles')
<style>.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}</style>
@endpush

@section('content')
<section class="content">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card x-card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('logistica.incidentes.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">ID de envío (opcional)</label>
                            <input name="externo_envio_id" class="form-control" value="{{ old('externo_envio_id') }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">ID pedido (opcional)</label>
                            <input type="number" name="pedidoid" class="form-control" value="{{ old('pedidoid') }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Tipo</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="Retraso">Retraso</option>
                                <option value="Calidad">Calidad</option>
                                <option value="Daño producto">Daño producto</option>
                                <option value="Faltante">Faltante</option>
                                <option value="Operación">Operación</option>
                                <option value="Reprogramación">Reprogramación</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group d-none" id="tipo-otro-div">
                        <label class="small font-weight-bold">Especificar tipo</label>
                        <input id="tipo_otro" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="5" required placeholder="Describa qué ocurrió y el contexto del incidente">{{ old('descripcion') }}</textarea>
                    </div>
                    <button class="btn btn-success"><i class="fas fa-save mr-1"></i>Registrar incidente</button>
                    <a href="{{ route('logistica.incidentes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('tipo').addEventListener('change', function () {
    const div = document.getElementById('tipo-otro-div');
    div.classList.toggle('d-none', this.value !== 'Otro');
});
document.querySelector('form').addEventListener('submit', function (e) {
    const tipo = document.getElementById('tipo');
    if (tipo.value === 'Otro') {
        const other = document.getElementById('tipo_otro').value.trim();
        if (!other) {
            e.preventDefault();
            alert('Especifique el tipo de incidente');
            return;
        }
        tipo.value = other;
    }
});
</script>
@endpush
