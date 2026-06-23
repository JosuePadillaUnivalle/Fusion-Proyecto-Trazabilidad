@php
    $tareasPlanta = session('operario_planta_nuevas_tareas', []);
    $clavesJson = json_encode(array_column($tareasPlanta, 'clave'));
@endphp

@if(! empty($tareasPlanta))
<div class="login-notif-scrim" id="plantaTareaScrim" aria-hidden="true"></div>

<div class="modal fade login-notif-modal-root" id="modalOperarioPlantaTarea" tabindex="-1" role="dialog" aria-labelledby="modalOperarioPlantaTareaTitulo" aria-hidden="true" data-backdrop="false" data-keyboard="true" data-login-notif-alcance="operario_planta" data-login-notif-claves="{{ $clavesJson }}">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content login-notif-modal login-notif-modal--planta">
            <div class="login-notif-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0 login-notif-modal__head">
                <div class="login-notif-modal__head-inner">
                    <span class="login-notif-modal__badge">
                        <i class="fas fa-bell"></i> Pendiente
                    </span>
                    <h5 class="modal-title mb-0 text-white" id="modalOperarioPlantaTareaTitulo">
                        @if(count($tareasPlanta) === 1)
                            Nueva tarea de transformación
                        @else
                            {{ count($tareasPlanta) }} tareas de transformación
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
                        <i class="fas fa-industry"></i>
                    </div>
                    <div>
                        <strong class="d-block login-notif-modal__highlight-title">
                            @if(count($tareasPlanta) === 1)
                                Tiene un trabajo asignado en planta
                            @else
                                Tiene {{ count($tareasPlanta) }} trabajos asignados en planta
                            @endif
                        </strong>
                        <span class="login-notif-modal__highlight-sub">
                            @if(count($tareasPlanta) === 1)
                                Abra la tarea y márquela como completada cuando termine la etapa.
                            @else
                                Elija con cuál comenzar y complete cada etapa desde su detalle.
                            @endif
                        </span>
                    </div>
                </div>
                <ul class="login-notif-modal__lista">
                    @foreach($tareasPlanta as $row)
                    <li class="login-notif-modal__item">
                        <div class="login-notif-modal__item-body">
                            <span class="login-notif-modal__codigo">{{ $row['proceso'] }}</span>
                            <span class="login-notif-modal__meta">
                                <i class="fas fa-tools mr-1"></i>{{ $row['maquina'] }}
                                <span class="mx-1">·</span>
                                <i class="fas fa-box mr-1"></i>{{ $row['lote'] }}
                            </span>
                        </div>
                        <a href="{{ $row['url'] }}" class="btn btn-sm login-notif-modal__cta">
                            <i class="fas fa-play mr-1"></i> Ir a la tarea
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer border-0 login-notif-modal__foot d-flex justify-content-between align-items-center">
                <a href="{{ route('tareas-planta.index') }}" class="btn btn-sm login-notif-modal__btn-secondary">
                    <i class="fas fa-list mr-1"></i> Mis tareas
                </a>
                <button type="button" class="btn btn-sm login-notif-modal__btn-cerrar" data-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('modalOperarioPlantaTarea');
    var scrimEl = document.getElementById('plantaTareaScrim');
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
    $modal.modal({ backdrop: false, keyboard: true, show: true });
});
</script>
@endpush
@endonce

@php session()->forget('operario_planta_nuevas_tareas'); @endphp
@endif
