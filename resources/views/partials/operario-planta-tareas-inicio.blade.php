@php
    $tareas = $tareasPendientes ?? collect();
    $total = (int) ($tareasPendientesCount ?? $tareas->count());
@endphp

@if(\App\Support\UsuarioRol::esOperarioPlanta(auth()->user()))
<div class="inicio-op-tareas card border-0 shadow-sm mb-3">
    <div class="inicio-op-tareas__head d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h2 class="inicio-op-tareas__title mb-0">
                <i class="fas fa-tasks text-warning mr-2"></i>Mis tareas
            </h2>
            <p class="inicio-op-tareas__sub mb-0">Etapas de transformación asignadas por el jefe de planta</p>
        </div>
        @if($total > 0)
            <span class="badge badge-warning px-3 py-2">{{ $total }} pendiente{{ $total === 1 ? '' : 's' }}</span>
        @endif
    </div>

    <div class="inicio-op-tareas__body">
        @forelse($tareas as $tarea)
            <a href="{{ route('tareas-planta.show', $tarea) }}" class="inicio-op-tareas__item">
                <span class="inicio-op-tareas__icon"><i class="fas fa-cog"></i></span>
                <span class="inicio-op-tareas__info">
                    <strong>{{ $tarea->proceso?->nombre ?? 'Etapa' }}</strong>
                    <span class="d-block text-muted small">
                        <i class="fas fa-tools mr-1"></i>{{ $tarea->maquina?->nombre ?? '—' }}
                        <span class="mx-1">·</span>
                        <i class="fas fa-box mr-1"></i>{{ $tarea->loteProduccion?->codigo_lote ?? '—' }}
                    </span>
                </span>
                <span class="inicio-op-tareas__go">
                    Ir <i class="fas fa-chevron-right ml-1"></i>
                </span>
            </a>
        @empty
            <div class="inicio-op-tareas__empty text-center text-muted py-4">
                <i class="fas fa-check-circle d-block mb-2" style="font-size:1.75rem;opacity:.45;color:#16a34a;"></i>
                No tiene tareas pendientes en este momento.
            </div>
        @endforelse
    </div>

    @if($total > 0)
        <div class="inicio-op-tareas__foot text-center">
            <a href="{{ route('tareas-planta.index') }}" class="btn btn-sm btn-outline-success font-weight-bold">
                <i class="fas fa-list mr-1"></i>Ver todas mis tareas
            </a>
        </div>
    @endif
</div>
@endif
