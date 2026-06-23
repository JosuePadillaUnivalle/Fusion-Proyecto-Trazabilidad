@php
    $solicitudes = session('mayorista_nuevas_solicitudes', []);
    $clavesJson = json_encode(array_column($solicitudes, 'clave'));
@endphp

@if(! empty($solicitudes))
<div class="login-notif-scrim" id="mayPedScrim" aria-hidden="true"></div>

<div class="modal fade login-notif-modal-root" id="modalMayoristaPedido" tabindex="-1" role="dialog" aria-labelledby="modalMayoristaPedidoTitulo" aria-hidden="true" data-backdrop="false" data-keyboard="true" data-login-notif-alcance="mayorista" data-login-notif-claves="{{ $clavesJson }}">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content login-notif-modal login-notif-modal--mayorista">
            <div class="login-notif-modal__accent" aria-hidden="true"></div>
            <div class="modal-header border-0 login-notif-modal__head">
                <div class="login-notif-modal__head-inner">
                    <span class="login-notif-modal__badge">
                        <i class="fas fa-bell"></i> Pendiente
                    </span>
                    <h5 class="modal-title mb-0 text-white" id="modalMayoristaPedidoTitulo">
                        @if(count($solicitudes) === 1)
                            Nueva solicitud de minorista
                        @else
                            {{ count($solicitudes) }} solicitudes de minoristas
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
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <strong class="d-block login-notif-modal__highlight-title">
                            @if(count($solicitudes) === 1)
                                Un minorista solicitó producto desde su almacén
                            @else
                                Hay {{ count($solicitudes) }} solicitudes por revisar
                            @endif
                        </strong>
                        <span class="login-notif-modal__highlight-sub">
                            Revise stock disponible y confirme el despacho hacia el punto de venta.
                        </span>
                    </div>
                </div>
                <ul class="login-notif-modal__lista">
                    @foreach($solicitudes as $row)
                    <li class="login-notif-modal__item">
                        <div class="login-notif-modal__item-body">
                            <span class="login-notif-modal__codigo">{{ $row['codigo'] }}</span>
                            <span class="login-notif-modal__meta">
                                @if(($row['tipo'] ?? '') === 'recepcion_planta')
                                    {{ $row['producto'] }} · {{ $row['minorista'] }}
                                @else
                                    {{ $row['minorista'] }} · {{ $row['producto'] }}
                                @endif
                            </span>
                            <span class="login-notif-modal__qty">{{ $row['cantidad'] }}</span>
                        </div>
                        <a href="{{ $row['url'] }}" class="btn btn-sm login-notif-modal__cta">
                            <i class="fas fa-search mr-1"></i> Revisar
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer border-0 login-notif-modal__foot d-flex justify-content-between align-items-center">
                <a href="{{ route('punto-venta.pedidos.index', ['ctx' => 'mayorista']) }}" class="btn btn-sm login-notif-modal__btn-secondary">
                    <i class="fas fa-list mr-1"></i> Bandeja de pedidos
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
    var modalEl = document.getElementById('modalMayoristaPedido');
    var scrimEl = document.getElementById('mayPedScrim');
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

@php session()->forget('mayorista_nuevas_solicitudes'); @endphp
@endif
