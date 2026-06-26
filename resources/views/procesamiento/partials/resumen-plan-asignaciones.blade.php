@php
    $asignaciones = $asignacionesPlanLote ?? collect();
@endphp

@if($asignaciones->isNotEmpty())
<div class="lp-plan-resumen mb-3" id="lp-plan-resumen">
    <h6 class="small font-weight-bold text-success mb-2"><i class="fas fa-clipboard-list mr-1"></i> Plan de asignaciones activo</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 bg-white">
            <thead class="thead-light">
                <tr>
                    <th>Etapa</th>
                    <th>Proceso</th>
                    <th>Operario</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asignaciones as $asig)
                <tr class="{{ $asig->estaPendiente() ? 'table-warning' : '' }}">
                    <td>{{ $asig->orden ?? '—' }}</td>
                    <td>{{ $asig->proceso?->nombre ?? '—' }}</td>
                    <td>{{ $asig->operador?->nombreCompleto() ?? '—' }}</td>
                    <td>
                        @if($asig->estaPendiente())
                            <span class="badge badge-warning">En curso</span>
                        @elseif($asig->estaProgramada())
                            <span class="badge badge-secondary">En cola</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="small text-muted mb-0 mt-2">
        <i class="fas fa-lock mr-1"></i> Las etapas asignadas no se pueden reordenar. Solo se ejecuta una a la vez, en orden.
    </p>
</div>
@endif
