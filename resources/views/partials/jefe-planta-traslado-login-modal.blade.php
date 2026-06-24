@php
    $traslados = session('jefe_planta_traslados_pendientes', []);
    $clavesJson = json_encode(array_column($traslados, 'clave'));
@endphp

@if(! empty($traslados))
<div class="login-notif-scrim" id="jefePlantaTrasladoScrim" aria-hidden="true"></div>

<div class="modal fade login-notif-modal-root" id="modalJefePlantaTraslado" tabindex="-1" role="dialog" aria-labelledby="modalJefePlantaTrasladoTitulo" aria-hidden="true" data-backdrop="false" data-keyboard="true" data-login-notif-alcance="jefe_planta_traslado" data-login-notif-claves="{{ $clavesJson }}">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content login-notif-modal login-notif-modal--planta">

            <div class="login-notif-modal__accent" aria-hidden="true"></div>

            <div class="modal-header border-0 login-notif-modal__head">

                <div class="login-notif-modal__head-inner">

                    <span class="login-notif-modal__badge">

                        <i class="fas fa-clipboard-check"></i> Aprobación

                    </span>

                    <h5 class="modal-title mb-0 text-white" id="modalJefePlantaTrasladoTitulo">

                        @if(count($traslados) === 1)

                            Traslado pendiente de aprobación

                        @else

                            {{ count($traslados) }} traslados por aprobar

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

                            @if(count($traslados) === 1)

                                Hay un envío hacia mayorista esperando su visto bueno

                            @else

                                Hay {{ count($traslados) }} envíos hacia mayorista por revisar

                            @endif

                        </strong>

                        <span class="login-notif-modal__highlight-sub">

                            Revise productos, cantidades y chofer asignado antes de autorizar la salida del camión.

                        </span>

                    </div>

                </div>

                <ul class="login-notif-modal__lista">

                    @foreach($traslados as $row)

                    <li class="login-notif-modal__item">

                        <div class="login-notif-modal__item-body">

                            <span class="login-notif-modal__codigo">{{ $row['codigo'] }}</span>

                            <span class="login-notif-modal__meta">{{ $row['destino'] }}</span>

                            <span class="login-notif-modal__qty">{{ $row['productos'] }}</span>

                        </div>

                        <a href="{{ $row['url'] }}" class="btn btn-sm login-notif-modal__cta">

                            <i class="fas fa-check-double mr-1"></i> Revisar

                        </a>

                    </li>

                    @endforeach

                </ul>

            </div>

            <div class="modal-footer border-0 login-notif-modal__foot d-flex justify-content-between align-items-center">

                <a href="{{ route('logistica.traslados-planta.index') }}" class="btn btn-sm login-notif-modal__btn-secondary">

                    <i class="fas fa-list mr-1"></i> Ver traslados

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

    var modalEl = document.getElementById('modalJefePlantaTraslado');

    var scrimEl = document.getElementById('jefePlantaTrasladoScrim');

    if (!window.jQuery || !modalEl || !scrimEl) return;



    if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);

    if (scrimEl.parentElement !== document.body) document.body.appendChild(scrimEl);



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



@php session()->forget('jefe_planta_traslados_pendientes'); @endphp

@endif

