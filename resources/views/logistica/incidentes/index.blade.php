@extends('layouts.app')

@section('title', 'Incidentes de envío | AgroFusion')
@section('page_title', 'Incidentes de envío')

@push('styles')
@include('partials.logistica-modulo-styles')
@include('logistica.partials.ops-reportes-styles')
@endpush

@section('content')
<div class="log-ops-wrap">
    <div class="log-ops-hero log-ops-hero--warn">
        <p class="log-ops-hero__title"><i class="fas fa-shield-alt text-warning mr-2"></i>Reportes operativos</p>
        <p class="log-ops-hero__text">
            Registre y dé seguimiento a retrasos, faltantes o daños durante el transporte. Resuelva incidentes con una nota de cierre.
        </p>
    </div>

    <div class="row log-ops-metrics">
        <div class="col-6 col-md-4 mb-2 mb-md-0">
            <div class="log-ops-metric">
                <span class="log-ops-metric__icon log-ops-metric__icon--blue"><i class="fas fa-clipboard-list"></i></span>
                <span>
                    <div class="log-ops-metric__val">{{ $resumenIncidentes['total'] ?? $incidentes->total() }}</div>
                    <div class="log-ops-metric__lbl">Incidentes (filtro actual)</div>
                </span>
            </div>
        </div>
        <div class="col-6 col-md-4 mb-2 mb-md-0">
            <div class="log-ops-metric">
                <span class="log-ops-metric__icon log-ops-metric__icon--rose"><i class="fas fa-exclamation-triangle"></i></span>
                <span>
                    <div class="log-ops-metric__val">{{ $resumenIncidentes['activos'] ?? 0 }}</div>
                    <div class="log-ops-metric__lbl">Abiertos / pendientes</div>
                </span>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="log-ops-metric">
                <span class="log-ops-metric__icon log-ops-metric__icon--green"><i class="fas fa-check-circle"></i></span>
                <span>
                    <div class="log-ops-metric__val">{{ $resumenIncidentes['resueltos'] ?? 0 }}</div>
                    <div class="log-ops-metric__lbl">Resueltos</div>
                </span>
            </div>
        </div>
    </div>

    <div class="log-ops-card">
        <div class="log-ops-card__head">
            <h2 class="log-ops-card__title"><i class="fas fa-shield-alt"></i> Incidentes de envío</h2>
            <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                <span class="log-ops-card__count">{{ $incidentes->total() }} registros</span>
                @can('incidentes.create')
                <a href="{{ route('logistica.incidentes.create') }}" class="btn btn-success btn-sm log-ops-btn-primary" style="padding:.4rem .9rem;font-size:.8rem">
                    <i class="fas fa-plus mr-1"></i> Nuevo incidente
                </a>
                @endcan
            </div>
        </div>

        <div class="log-ops-filtros">
            @include('partials.modulo-filtros-form', [
                'action' => route('logistica.incidentes.index'),
                'campos' => [
                    ['name' => 'q', 'label' => 'Buscar', 'placeholder' => 'Descripción, tipo, envío…', 'col' => 'col-md-3'],
                    ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'col' => 'col-md-2', 'options' => [
                        'abierto' => 'Abierto',
                        'pendiente' => 'Pendiente',
                        'resuelto' => 'Resuelto',
                    ]],
                    ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'col' => 'col-md-2',
                        'options' => ($tiposDisponibles ?? collect())->mapWithKeys(fn ($t) => [$t => $t])->all()],
                    ['name' => 'envio', 'label' => 'ID envío', 'placeholder' => 'ENV-…', 'col' => 'col-md-2'],
                    ['name' => 'desde', 'label' => 'Desde', 'type' => 'date', 'col' => 'col-md-1'],
                    ['name' => 'hasta', 'label' => 'Hasta', 'type' => 'date', 'col' => 'col-md-1'],
                ],
            ])
        </div>

        <div class="table-responsive">
            <table class="table mb-0 log-ops-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Envío / pedido</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Reportado por</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidentes as $incidente)
                        @php
                            $chipEstado = match ($incidente->estado) {
                                'resuelto' => 'log-ops-chip--resuelto',
                                'pendiente' => 'log-ops-chip--pendiente',
                                default => 'log-ops-chip--abierto',
                            };
                            $ref = $incidente->externo_envio_id ?? ('Pedido #'.$incidente->pedidoid);
                        @endphp
                        <tr>
                            <td class="td-muted">{{ optional($incidente->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="td-ref">{{ $ref }}</td>
                            <td><span class="log-ops-tipo-pill">{{ $incidente->tipo }}</span></td>
                            <td>
                                <span class="log-ops-chip {{ $chipEstado }}">{{ ucfirst($incidente->estado) }}</span>
                            </td>
                            <td>{{ $incidente->reportadoPor?->nombreusuario ?? 'N/D' }}</td>
                            <td class="text-right text-nowrap">
                                <div class="log-ops-actions log-ops-actions--incidentes justify-content-end">
                                    <a href="{{ route('logistica.incidentes.show', $incidente) }}"
                                       class="log-ops-btn-icon log-ops-btn-icon--view" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('incidentes.update')
                                    <a href="{{ route('logistica.incidentes.edit', $incidente) }}"
                                       class="log-ops-btn-icon log-ops-btn-icon--edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('incidentes.delete')
                                    <form action="{{ route('logistica.incidentes.destroy', $incidente) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar este incidente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="log-ops-btn-icon log-ops-btn-icon--del" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @if($incidente->estado !== 'resuelto')
                                        @can('incidentes.resolve')
                                        <button type="button"
                                                class="log-ops-btn-icon log-ops-btn-icon--resolve btn-resolver-inc"
                                                title="Resolver incidente"
                                                data-ref="{{ $ref }}"
                                                data-url="{{ route('logistica.incidentes.resolve', $incidente) }}">
                                            <i class="fas fa-check mr-1"></i> Resolver
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="log-ops-empty">
                                    <i class="fas fa-shield-alt"></i>
                                    No hay incidentes con esos filtros.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($incidentes->hasPages())
        <div class="log-ops-footer">{{ $incidentes->links() }}</div>
        @endif
    </div>
</div>

@can('incidentes.resolve')
<div class="modal fade log-ops-modal" id="modalResolverIncidente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="formResolverIncidente" action="#">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-circle text-success mr-2"></i>Resolver incidente</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Cierre el incidente de <strong id="resolverIncRef">—</strong> con una nota breve.
                    </p>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-uppercase text-muted">Nota de resolución</label>
                        <textarea name="nota_resolucion" class="form-control" rows="3" required
                                  placeholder="Indique qué acción se tomó o el resultado del caso…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success log-ops-btn-primary">
                        <i class="fas fa-check mr-1"></i> Marcar como resuelto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@can('incidentes.resolve')
@push('scripts')
<script>
document.querySelectorAll('.btn-resolver-inc').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var form = document.getElementById('formResolverIncidente');
        var ref = document.getElementById('resolverIncRef');
        if (!form) return;
        form.action = btn.getAttribute('data-url') || '#';
        if (ref) ref.textContent = btn.getAttribute('data-ref') || '—';
        form.querySelector('textarea[name="nota_resolucion"]').value = '';
        $('#modalResolverIncidente').modal('show');
    });
});
</script>
@endpush
@endcan
