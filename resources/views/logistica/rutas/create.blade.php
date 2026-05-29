@extends('layouts.app')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.section-title{font-weight:700;color:#2c5530}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Crear ruta multi-entrega</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card x-card">
            <div class="card-body">
                <form method="POST" action="{{ route('logistica.rutas.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nombre de ruta</label>
                            <input name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Transportista</label>
                            @php $drivers = \App\Models\Usuario::where('role','transportista')->where('activo', true)->orderBy('nombre')->get(); @endphp
                            <select name="transportista_usuarioid" class="form-control">
                                <option value="">-- Ninguno --</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->usuarioid }}">{{ $d->nombre }} {{ $d->apellido }} ({{ $d->nombreusuario }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Fecha salida</label>
                            <input type="datetime-local" name="fecha_salida" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h5 class="section-title">Paradas iniciales (opcional)</h5>
                    <div id="paradas-wrapper">
                        <div class="row parada-item">
                            <div class="col-md-5 form-group">
                                <label>Destino</label>
                                <input name="paradas[0][destino]" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Envío</label>
                                <input name="paradas[0][externo_envio_id]" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Pedido ID</label>
                                <input type="number" name="paradas[0][pedidoid]" class="form-control">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary mb-3" id="add-parada">Agregar parada</button>
                    <br>
                    <button class="btn btn-primary">Guardar ruta</button>
                    <a href="{{ route('logistica.rutas.index') }}" class="btn btn-default">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
(() => {
    const wrapper = document.getElementById('paradas-wrapper');
    const addBtn = document.getElementById('add-parada');
    let idx = 1;

    addBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'row parada-item';
        row.innerHTML = `
            <div class="col-md-5 form-group">
                <label>Destino</label>
                <input name="paradas[${idx}][destino]" class="form-control">
            </div>
            <div class="col-md-4 form-group">
                <label>Envío</label>
                <input name="paradas[${idx}][externo_envio_id]" class="form-control">
            </div>
            <div class="col-md-2 form-group">
                <label>Pedido ID</label>
                <input type="number" name="paradas[${idx}][pedidoid]" class="form-control">
            </div>
            <div class="col-md-1 form-group d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-parada">X</button>
            </div>
        `;
        wrapper.appendChild(row);
        idx++;
    });

    wrapper.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-parada')) {
            e.target.closest('.parada-item').remove();
        }
    });
})();
</script>
@endsection

