@php
    $envios = session('minorista_envios_pendientes', []);
    $clavesJson = json_encode(array_column($envios, 'clave'));
@endphp

@if(! empty($envios))
<div class="login-notif-scrim" id="minoristaEnvScrim" aria-hidden="true"></div>

<div class="modal fade login-notif-modal-root" id="modalMinoristaEnvio" tabindex="-1" role="dialog"
     aria-labelledby="modalMinoristaEnvioTitulo" aria-hidden="true"
     data-backdrop="false" data-keyboard="true"
     data-login-notif-alcance="minorista" data-login-notif-claves="{{ $clavesJson }}">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content login-notif-modal login-notif-modal--minorista">
            <div class="login-notif-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0 login-notif-modal__head">
                <div class="login-notif-modal__head-inner">
                    <span class="login-notif-modal__badge">
                        <i class="fas fa-store"></i> Punto de venta
                    </span>
                    <h5 class="modal-title mb-0 text-white" id="modalMinoristaEnvioTitulo">
                        @if(count($envios) === 1)
                            Envío por confirmar
                        @else
                            {{ count($envios) }} envíos por confirmar
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
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div>
                        <strong class="d-block login-notif-modal__highlight-title">
                            El mayorista inició un envío hacia su punto de venta
                        </strong>
                        <span class="login-notif-modal__highlight-sub">
                            Confirme o reciba la mercadería cuando corresponda.
                        </span>
                    </div>
                </div>
                <ul class="login-notif-modal__lista">
                    @foreach($envios as $row)
                    <li class="login-notif-modal__item">
                        <div class="login-notif-modal__item-body">
                            <span class="login-notif-modal__codigo">{{ $row['codigo'] }}</span>
                            <span class="login-notif-modal__meta">
                                {{ $row['producto'] }}
                                <span class="mx-1">·</span>
                                <i class="fas fa-warehouse mr-1"></i>{{ $row['mayorista'] }}
                            </span>
                        </div>
                        <a href="{{ $row['url'] }}" class="btn btn-sm login-notif-modal__cta">
                            <i class="fas fa-arrow-right mr-1"></i> Ver envío
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer border-0 login-notif-modal__foot d-flex justify-content-between align-items-center">
                <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-sm login-notif-modal__btn-secondary">
                    <i class="fas fa-list mr-1"></i> Mis pedidos
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
    var modalEl = document.getElementById('modalMinoristaEnvio');
    var scrimEl = document.getElementById('minoristaEnvScrim');
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

@php session()->forget('minorista_envios_pendientes'); @endphp
@endif
