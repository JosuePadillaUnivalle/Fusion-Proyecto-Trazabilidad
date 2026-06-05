@extends('layouts.app')

@section('title', 'Editar documento | AgroFusion')
@section('page_title', 'Editar documento')

@section('content')
<section class="content">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card card-outline card-success elevation-1">
            <div class="card-header bg-white py-3"><h5 class="mb-0 font-weight-bold">Editar documento</h5></div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('logistica.documentos.update', $documento) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Título</label>
                            <input name="titulo" class="form-control" required value="{{ old('titulo', $documento->titulo) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Tipo</label>
                            <select name="tipo_documento" class="form-control" required>
                                @foreach(['pod' => 'POD / comprobante entrega', 'nota_entrega' => 'Nota entrega', 'guia_transporte' => 'Guía transporte', 'evidencia' => 'Evidencia'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('tipo_documento', $documento->tipo_documento) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">ID de envío</label>
                            <input name="externo_envio_id" class="form-control" value="{{ old('externo_envio_id', $documento->externo_envio_id) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">ID pedido</label>
                            <input type="number" name="pedidoid" class="form-control" value="{{ old('pedidoid', $documento->pedidoid) }}">
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="small font-weight-bold">Reemplazar archivo <span class="text-muted">(opcional)</span></label>
                            <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                    <button class="btn btn-success"><i class="fas fa-save mr-1"></i>Guardar</button>
                    <a href="{{ route('logistica.documentos.show', $documento) }}" class="btn btn-outline-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
