@php
    $actividades = session('agricultor_actividades_pendientes', []);
    $clavesJson = json_encode(array_column($actividades, 'clave'));
@endphp

@if(! empty($actividades))
<div class="login-notif-scrim" id="agricultorActScrim" aria-hidden="true"></div>

<div class="modal fade login-notif-modal-root" id="modalAgricultorActividad" tabindex="-1" role="dialog"
     aria-labelledby="modalAgricultorActividadTitulo" aria-hidden="true"
     data-backdrop="false" data-keyboard="true"
     data-login-notif-alcance="agricultor" data-login-notif-claves="{{ $clavesJson }}">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content login-notif-modal login-notif-modal--agricultor">
            <div class="login-notif-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0 login-notif-modal__head">
                <div class="login-notif-modal__head-inner">
                    <span class="login-notif-modal__badge">
                        <i class="fas fa-seedling"></i> Campo
                    </span>
                    <h5 class="modal-title mb-0 text-white" id="modalAgricultorActividadTitulo">
                        @if(count($actividades) === 1)
                            Tarea pendiente en campo
                        @else
                            {{ count($actividades) }} tareas pendientes en campo
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
                    @foreach($actividades as $row)
                    <li class="login-notif-modal__item">
                        <div class="login-notif-modal__item-body">
                            <span class="login-notif-modal__codigo">{{ $row['titulo'] }}</span>
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('modalAgricultorActividad');
    var scrimEl = document.getElementById('agricultorActScrim');
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

@php session()->forget('agricultor_actividades_pendientes'); @endphp
@endif
