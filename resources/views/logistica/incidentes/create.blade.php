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

