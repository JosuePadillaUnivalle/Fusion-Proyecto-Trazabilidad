@php
    $asignaciones = session('transportista_nuevas_asignaciones', []);
@endphp

@if(! empty($asignaciones))
<div class="login-notif-scrim" id="transpAsignScrim" aria-hidden="true"></div>

<div class="modal fade login-notif-modal-root" id="modalTransportistaAsignacion" tabindex="-1" role="dialog" aria-labelledby="modalTransportistaAsignacionTitulo" aria-hidden="true" data-backdrop="false" data-keyboard="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content login-notif-modal login-notif-modal--transportista">
            <div class="login-notif-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0 login-notif-modal__head">
                <div class="login-notif-modal__head-inner">
                    <span class="login-notif-modal__badge">
                        <i class="fas fa-truck"></i> Asignación
                    </span>
                    <h5 class="modal-title mb-0 text-white" id="modalTransportistaAsignacionTitulo">
                        @if(count($asignaciones) === 1)
                            Nuevo envío asignado
                        @else
                            {{ count($asignaciones) }} envíos asignados
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
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div>
                        <strong class="d-block login-notif-modal__highlight-title">
                            @if(count($asignaciones) === 1)
                                Tiene un envío listo para gestionar
                            @else
                                Tiene {{ count($asignaciones) }} envíos asignados
                            @endif
                        </strong>
                        <span class="login-notif-modal__highlight-sub">
                            Abra el envío para revisar condiciones del vehículo, ruta y cierre operativo.
                        </span>
                    </div>
                </div>
                <ul class="login-notif-modal__lista">
                    @foreach($asignaciones as $row)
                    <li class="login-notif-modal__item">
                        <div class="login-notif-modal__item-body">
                            <span class="login-notif-modal__codigo">{{ $row['codigo'] }}</span>
                            <span class="login-notif-modal__meta">{{ $row['producto'] }}</span>
                        </div>
                        <a href="{{ $row['url'] }}" class="btn btn-sm login-notif-modal__cta">
                            <i class="fas fa-arrow-right mr-1"></i> Ver envío
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer border-0 login-notif-modal__foot d-flex justify-content-between align-items-center">
                <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-sm login-notif-modal__btn-secondary">
                    <i class="fas fa-list mr-1"></i> Mis envíos
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
    var modalEl = document.getElementById('modalTransportistaAsignacion');
    var scrimEl = document.getElementById('transpAsignScrim');
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

@php session()->forget('transportista_nuevas_asignaciones'); @endphp
@endif
