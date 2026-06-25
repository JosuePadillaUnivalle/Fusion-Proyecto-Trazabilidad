@php
    $modalId = $modalId ?? 'modalAgricultorTareasInicio';
    $tareas = $tareas ?? [];
    $autoShow = $autoShow ?? false;
    $marcarVistas = $marcarVistas ?? false;
    $clavesJson = json_encode(array_column($tareas, 'clave'));
    $scrimId = $modalId.'Scrim';
@endphp

@if(! empty($tareas))
<div class="login-notif-scrim" id="{{ $scrimId }}" aria-hidden="true"></div>

<div class="modal fade login-notif-modal-root" id="{{ $modalId }}" tabindex="-1" role="dialog"
     aria-labelledby="{{ $modalId }}Titulo" aria-hidden="true"
     data-backdrop="false" data-keyboard="true"
     @if($marcarVistas)
         data-login-notif-alcance="agricultor" data-login-notif-claves="{{ $clavesJson }}"
     @endif>
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content login-notif-modal login-notif-modal--agricultor">
            <div class="login-notif-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0 login-notif-modal__head">
                <div class="login-notif-modal__head-inner">
                    <span class="login-notif-modal__badge">
                        <i class="fas fa-seedling"></i> Campo
                    </span>
                    <h5 class="modal-title mb-0 text-white" id="{{ $modalId }}Titulo">
                        @if(count($tareas) === 1)
                            Tarea pendiente en campo
                        @else
                            {{ count($tareas) }} tareas pendientes en campo
                        @endif
                    </h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body login-notif-modal__body">
                <div class="login-notif-modal__highlight">
                    <div class="login-notif-modal__highlight-icon">
                        <i class="fas fa-tractor"></i>
                    </div>
                    <div>
                        <strong class="d-block login-notif-modal__highlight-title">
                            Tiene trabajo asignado por completar
                        </strong>
                        <span class="login-notif-modal__highlight-sub">
                            Abra la actividad, regístrela y súbala con foto cuando termine.
                        </span>
                    </div>
                </div>
                <ul class="login-notif-modal__lista">
                    @foreach($tareas as $row)
                    <li class="login-notif-modal__item">
                        <div class="login-notif-modal__item-body">
                            <span class="login-notif-modal__codigo">
                                {{ $row['titulo'] }}
                                @if(!empty($row['prioridad']))
                                    <span class="badge badge-{{ $row['prioridad_badge'] ?? 'secondary' }} ml-1">
                                        {{ ucfirst($row['prioridad']) }}
                                    </span>
                                @endif
                            </span>
                            <span class="login-notif-modal__meta">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $row['lote'] }}
                            </span>
                        </div>
                        <a href="{{ $row['url'] }}" class="btn btn-sm login-notif-modal__cta">
                            <i class="fas fa-arrow-right mr-1"></i> Ver tarea
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer border-0 login-notif-modal__foot d-flex justify-content-between align-items-center">
                <a href="{{ route('actividades.index') }}" class="btn btn-sm login-notif-modal__btn-secondary">
                    <i class="fas fa-list mr-1"></i> Mis actividades
                </a>
                <button type="button" class="btn btn-sm login-notif-modal__btn-cerrar" data-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.agricultor-tareas-card__icon {
    width: 2.35rem;
    height: 2.35rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #15803d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.agricultor-tareas-card__trigger:hover strong { color: #166534 !important; }
</style>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById(@json($modalId));
    var scrimEl = document.getElementById(@json($scrimId));
    if (!window.jQuery || !modalEl || !scrimEl) return;
    var $modal = window.jQuery(modalEl);
    $modal.on('show.bs.modal', function () {
        scrimEl.classList.add('is-visible');
        document.body.classList.add('modal-open', 'login-notif-modal-open');
    }).on('hidden.bs.modal', function () {
        scrimEl.classList.remove('is-visible');
        document.body.classList.remove('modal-open', 'login-notif-modal-open');
    });
    scrimEl.addEventListener('click', function () { $modal.modal('hide'); });
    @if($autoShow)
    $modal.modal({ backdrop: false, keyboard: true, show: true });
    @endif
});
</script>
@endpush
@endif
