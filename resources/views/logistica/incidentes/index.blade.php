@extends('layouts.app')

@section('title', 'Incidentes de envío | AgroFusion')
@section('page_title', 'Incidentes de envío')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.inc-resolver-form{margin-top:.5rem;padding-top:.5rem;border-top:1px dashed #eee;width:100%}
</style>
@endpush

@section('content')
<section class="content">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card card-outline card-success card-modulo-main elevation-1">
            <x-modulo-index-header
                titulo="Incidentes de envío"
                icono="fa-shield-alt"
                :registros="$incidentes->total()"
                :nuevo-href="route('logistica.incidentes.create')"
                nuevo-text="Nuevo incidente"
                nuevo-can="incidentes.create"
            />

            @include('partials.modulo-filtros-form', [
                'action' => route('logistica.incidentes.index'),
                'campos' => [
                    ['name' => 'q', 'label' => 'Buscar', 'placeholder' => 'Descripción, tipo, envío...', 'col' => 'col-md-3'],
                    ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'col' => 'col-md-2', 'options' => [
                        'abierto' => 'Abierto',
                        'pendiente' => 'Pendiente',
                        'resuelto' => 'Resuelto',
                    ]],
                    ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'col' => 'col-md-2',
                        'options' => ($tiposDisponibles ?? collect())->mapWithKeys(fn ($t) => [$t => $t])->all()],
                    ['name' => 'envio', 'label' => 'ID envío', 'placeholder' => 'ENV-...', 'col' => 'col-md-2'],
                    ['name' => 'desde', 'label' => 'Desde', 'type' => 'date', 'col' => 'col-md-1'],
                    ['name' => 'hasta', 'label' => 'Hasta', 'type' => 'date', 'col' => 'col-md-1'],
                ],
            ])

            <div class="card-body table-responsive p-0">
                <table class="table table-modulo table-hover mb-0 modulo-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Envío/Pedido</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Reportado por</th>
                            <th style="min-width:240px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidentes as $incidente)
                            <tr>
                                <td>{{ optional($incidente->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $incidente->externo_envio_id ?? ('Pedido #'.$incidente->pedidoid) }}</td>
                                <td>{{ $incidente->tipo }}</td>
                                <td>
                                    <span class="badge badge-pill px-3 py-2 badge-{{
                                        $incidente->estado === 'resuelto' ? 'success' :
                                        ($incidente->estado === 'pendiente' ? 'warning' : 'danger')
                                    }}">{{ ucfirst($incidente->estado) }}</span>
                                </td>
                                <td>{{ $incidente->reportadoPor?->nombreusuario ?? 'N/D' }}</td>
                                <td>
                                    <div class="crud-actions">
                                        <a href="{{ route('logistica.incidentes.show', $incidente) }}"
                                           class="btn btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('incidentes.update')
                                        <a href="{{ route('logistica.incidentes.edit', $incidente) }}"
                                           class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('incidentes.delete')
                                        <form action="{{ route('logistica.incidentes.destroy', $incidente) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar este incidente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                    @if($incidente->estado !== 'resuelto')
                                        @can('incidentes.resolve')
                                        <form method="POST" action="{{ route('logistica.incidentes.resolve', $incidente) }}" class="inc-resolver-form">
                                            @csrf
                                            @method('PATCH')
                                            <div class="input-group input-group-sm">
                                                <input name="nota_resolucion" class="form-control" placeholder="Nota de resolución">
                                                <div class="input-group-append">
                                                    <button class="btn btn-success px-3">Resolver</button>
                                                </div>
                                            </div>
                                        </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-shield-alt fa-2x mb-2 d-block"></i>No hay incidentes con esos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-3">{{ $incidentes->links() }}</div>
        </div>
    </div>
</section>
@endsection
