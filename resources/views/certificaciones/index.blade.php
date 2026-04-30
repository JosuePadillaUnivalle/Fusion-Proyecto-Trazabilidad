@extends('layouts.app')

@section('title', 'Certificaciones')
@section('page_title', 'Certificaciones')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Aquí certificas lotes trazables y consultas certificados ya emitidos.
    </div>

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Certificaciones de lote</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><strong>Lotes disponibles para certificar</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Cultivo</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($lotes as $lote)
                                <tr>
                                    <td>{{ $lote->loteid }}</td>
                                    <td>{{ $lote->nombre }}</td>
                                    <td>{{ $lote->cultivo->nombre ?? '-' }}</td>
                                    <td>{{ $lote->estadoTipo->nombre ?? '-' }}</td>
                                    <td>
                                        @can('certificaciones.create')
                                            <form action="{{ route('certificaciones.store') }}" method="POST" class="d-inline-flex gap-2">
                                                @csrf
                                                <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                                                <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Observación (opcional)">
                                                <button class="btn btn-sm btn-success" type="submit">Certificar</button>
                                            </form>
                                        @else
                                            <span class="badge badge-secondary">Solo lectura</span>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No hay lotes disponibles para certificar. Revisa que existan lotes cosechados o en proceso.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><strong>Certificados emitidos</strong></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($certificados as $cert)
                            <li class="list-group-item">
                                <div class="fw-bold">{{ $cert->codigo_certificado }}</div>
                                <div class="small text-muted">Lote #{{ $cert->loteid }} - {{ $cert->lote->nombre ?? 'N/D' }}</div>
                                <div class="small text-muted">Fecha: {{ $cert->fecha_certificacion?->format('d/m/Y H:i') }}</div>
                                @if($cert->observaciones)
                                    <div class="small mt-1">{{ $cert->observaciones }}</div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aún no hay certificados emitidos.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

