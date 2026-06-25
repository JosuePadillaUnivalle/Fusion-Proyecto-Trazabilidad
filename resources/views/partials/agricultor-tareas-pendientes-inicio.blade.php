@php
    $tareas = $tareasPendientes ?? [];
    $total = $totalTareasPendientes ?? count($tareas);
@endphp

@if($total > 0)
<div class="card border-0 mb-3 agricultor-tareas-card" style="border-left:4px solid #10b981 !important; box-shadow:0 4px 14px rgba(16,185,129,.12);">
    <div class="card-body py-3">
        <button type="button"
                class="btn btn-link p-0 text-left border-0 bg-transparent w-100 text-body agricultor-tareas-card__trigger"
                data-toggle="modal"
                data-target="#modalAgricultorTareasInicio"
                style="text-decoration:none;">
            <div class="d-flex align-items-center justify-content-between" style="gap:.75rem;">
                <div class="d-flex align-items-center" style="gap:.65rem;">
                    <span class="agricultor-tareas-card__icon">
                        <i class="fas fa-clipboard-list"></i>
                    </span>
                    <div>
                        <strong style="font-size:.95rem;color:#14532d;">
                            Tienes {{ $total }} {{ $total === 1 ? 'tarea pendiente' : 'tareas pendientes' }}
                        </strong>
                        <span class="d-block text-muted" style="font-size:.78rem;">
                            Toque para ver el detalle y abrir cada actividad en campo
                        </span>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-success" style="font-size:.85rem;opacity:.75;"></i>
            </div>
        </button>
    </div>
</div>

@include('partials.agricultor-tareas-pendientes-modal', [
    'modalId' => 'modalAgricultorTareasInicio',
    'tareas' => $tareas,
    'autoShow' => false,
])
@endif
