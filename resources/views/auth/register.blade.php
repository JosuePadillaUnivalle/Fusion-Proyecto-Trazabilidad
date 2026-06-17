@extends('layouts.auth')

@section('title', 'Registro | AgroFusion')

@push('styles')
<style>
    .form-side { overflow-y: auto; padding: 40px 60px; }
    .form-row { display: flex; gap: 20px; }
    .form-row .form-group { flex: 1; min-width: 0; }
    .rol-opciones { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 4px; }
    .rol-opcion { position: relative; }
    .rol-opcion input { position: absolute; opacity: 0; }
    .rol-opcion label {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 14px 8px; border: 2px solid #e2e8f0; border-radius: 10px;
        cursor: pointer; text-align: center; font-size: .85rem; font-weight: 600;
        transition: border-color .15s, background .15s;
    }
    .rol-opcion input:checked + label { border-color: #10b981; background: #ecfdf5; color: #059669; }
    .rol-opcion label i { font-size: 1.2rem; }
    .phone-line {
        display: grid;
        grid-template-columns: minmax(100px, 118px) minmax(0, 1fr);
        gap: 8px;
        width: 100%;
    }
    .phone-prefijo-combo {
        position: relative;
        display: flex;
        align-items: stretch;
        min-width: 0;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 10px;
        box-sizing: border-box;
    }
    .phone-prefijo-combo:focus-within {
        border-color: rgba(16, 185, 129, .5);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .12);
    }
    .phone-prefijo-combo input {
        flex: 1;
        min-width: 0;
        width: 100%;
        padding: 11px 6px 11px 10px;
        border: none;
        background: transparent;
        color: #e2e8f0;
        font-size: .88rem;
        font-family: 'Inter', sans-serif;
        outline: none;
        box-sizing: border-box;
    }
    .phone-prefijo-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 0 10px;
        border: none;
        background: transparent;
        color: #94a3b8;
        font-size: .72rem;
        cursor: pointer;
        line-height: 1;
    }
    .phone-prefijo-btn:hover { color: #e2e8f0; }
    .phone-pais-select-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .phone-pais-select-hidden option {
        color: #0f172a;
        background: #ffffff;
    }
    .phone-line .phone-numero-wrap input {
        width: 100%;
        min-width: 0;
        padding: 11px 10px 11px 36px;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 10px;
        color: #e2e8f0;
        font-size: .88rem;
        font-family: 'Inter', sans-serif;
        outline: none;
        box-sizing: border-box;
    }
    .phone-line .phone-numero-wrap input:focus {
        border-color: rgba(16, 185, 129, .5);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .12);
    }
    .phone-line .phone-numero-wrap { position: relative; min-width: 0; }
    .phone-line .phone-numero-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #475569;
        font-size: .82rem;
        pointer-events: none;
    }
    @media (max-width: 992px) {
        .form-row { flex-direction: column; gap: 0; }
        .rol-opciones { grid-template-columns: 1fr; }
        .phone-line {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="form-header">
    <h2>Solicitar acceso</h2>
    <p>Registro para jefes de producción agrícola, jefes de planta, transportistas o minoristas. Un administrador revisará tu solicitud.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<form method="POST" action="{{ route('register.post') }}">
    @csrf

    <div class="form-group">
        <label>¿Cómo deseas participar?</label>
        <div class="rol-opciones">
            @foreach($rolesRegistro as $rol)
            <div class="rol-opcion">
                <input type="radio" name="rol_solicitado" id="rol_{{ $rol }}" value="{{ $rol }}"
                    {{ old('rol_solicitado') === $rol ? 'checked' : '' }} required>
                <label for="rol_{{ $rol }}">
                    @if($rol === 'jefe_agricultor')
                        <i class="fas fa-tractor"></i>
                        <span>Jefe Agricultor</span>
                    @elseif($rol === 'jefe_planta')
                        <i class="fas fa-industry"></i>
                        <span>Jefe Planta</span>
                    @elseif($rol === 'transportista')
                        <i class="fas fa-truck"></i>
                        <span>Transportista</span>
                    @elseif($rol === 'minorista')
                        <i class="fas fa-store"></i>
                        <span>Minorista</span>
                    @else
                        <i class="fas fa-user"></i>
                        <span>{{ ucfirst(str_replace('_', ' ', $rol)) }}</span>
                    @endif
                </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <div class="input-wrapper">
                <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}" required class="form-control"
                    placeholder="Juan" autocomplete="given-name" data-solo-letras>
                <i class="fas fa-user"></i>
            </div>
        </div>
        <div class="form-group">
            <label for="apellido">Apellido</label>
            <div class="input-wrapper">
                <input id="apellido" type="text" name="apellido" value="{{ old('apellido') }}" required class="form-control"
                    placeholder="Pérez" autocomplete="family-name" data-solo-letras>
                <i class="fas fa-user"></i>
            </div>
        </div>
    </div>

    <div id="campos-transportista" style="display: none;">
        @include('envios.partials.campo-licencias-checkboxes', [
            'tiposLicencia' => $tiposLicencia ?? config('tipos_licencia_bolivia', []),
            'inputPrefix' => 'reg_',
            'licenciasTema' => 'dark',
        ])
    </div>

    @php
        $prefijosTelefono = $prefijosTelefono ?? config('telefono_prefijos', []);
        $telefonoCompleto = trim((string) old('telefono', ''));
        $prefijoTelefono = '+591';
        $numeroTelefono = '';
        if (preg_match('/^(\+\d{1,5})\s*(.*)$/u', $telefonoCompleto, $coincidencia)) {
            $prefijoTelefono = $coincidencia[1];
            $numeroTelefono = trim($coincidencia[2]);
        } elseif ($telefonoCompleto !== '') {
            $numeroTelefono = $telefonoCompleto;
        }
    @endphp

    <div class="form-group">
        <label for="telefono_prefijo">Teléfono</label>
        <div class="phone-line">
            <div class="phone-prefijo-combo">
                <input type="text" id="telefono_prefijo" value="{{ $prefijoTelefono }}"
                    maxlength="8" placeholder="+591" inputmode="tel"
                    autocomplete="off" spellcheck="false"
                    aria-label="Prefijo telefónico">
                <button type="button" id="telefono_pais_btn" class="phone-prefijo-btn"
                    aria-label="Elegir país" title="Elegir país">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <select id="telefono_pais" class="phone-pais-select-hidden" tabindex="-1"
                    aria-hidden="true">
                    @foreach($prefijosTelefono as $item)
                        <option value="{{ $item['code'] }}"
                            @selected($prefijoTelefono === $item['code'])>{{ $item['country'] }} ({{ $item['code'] }})</option>
                    @endforeach
                </select>
            </div>
            <div class="phone-numero-wrap">
                <input type="text" id="telefono_numero" value="{{ $numeroTelefono }}" required
                    placeholder="70000000" inputmode="numeric" autocomplete="tel-national"
                    aria-label="Número de teléfono">
                <i class="fas fa-phone"></i>
            </div>
        </div>
        <input type="hidden" name="telefono" id="telefono" value="{{ old('telefono') }}">
    </div>

    <div class="form-group">
        <label for="ci_nit">CI / NIT</label>
        <div class="input-wrapper">
            <input id="ci_nit" type="text" name="ci_nit" value="{{ old('ci_nit') }}" required class="form-control"
                placeholder="1234567 LP" data-alfanumerico>
            <i class="fas fa-id-card"></i>
        </div>
    </div>

    <div class="form-group">
        <label for="email">Correo electrónico</label>
        <div class="input-wrapper">
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="tu@correo.com">
            <i class="fas fa-envelope"></i>
        </div>
    </div>

    <div class="form-group">
        <label for="carta_motivacion">Carta de motivación</label>
        <textarea id="carta_motivacion" name="carta_motivacion" rows="4" required class="form-control"
            placeholder="Cuéntanos por qué deseas formar parte de AgroFusion…">{{ old('carta_motivacion') }}</textarea>
        <small class="text-muted">Mínimo 30 caracteres.</small>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-wrapper">
                <input id="password" type="password" name="password" required class="form-control" placeholder="••••••••">
                <i class="fas fa-lock"></i>
            </div>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirmar</label>
            <div class="input-wrapper">
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control" placeholder="••••••••">
                <i class="fas fa-lock"></i>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-login" style="margin-top: 10px;">
        <i class="fas fa-paper-plane"></i>
        Enviar solicitud
    </button>

    <div class="form-footer">
        <p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var bloque = document.getElementById('campos-transportista');
    var radios = document.querySelectorAll('input[name="rol_solicitado"]');
    var telefonoHidden = document.getElementById('telefono');
    var telefonoPrefijo = document.getElementById('telefono_prefijo');
    var telefonoNumero = document.getElementById('telefono_numero');
    var telefonoPais = document.getElementById('telefono_pais');
    var telefonoPaisBtn = document.getElementById('telefono_pais_btn');
    var form = document.querySelector('form[action="{{ route('register.post') }}"]');

    function actualizar() {
        var rol = document.querySelector('input[name="rol_solicitado"]:checked');
        var esTransportista = rol && rol.value === 'transportista';
        bloque.style.display = esTransportista ? 'block' : 'none';
        if (!esTransportista) {
            bloque.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
                cb.checked = false;
                cb.disabled = false;
            });
        }
    }

    function filtrarSoloLetras(input) {
        if (!input) return;
        input.addEventListener('input', function () {
            var valor = this.value.replace(/[^\p{L}\s]/gu, '').replace(/\s{2,}/g, ' ');
            if (this.value !== valor) this.value = valor;
        });
    }

    function filtrarAlfanumerico(input) {
        if (!input) return;
        input.addEventListener('input', function () {
            var valor = this.value.replace(/[^\p{L}\d\s]/gu, '').replace(/\s{2,}/g, ' ');
            if (this.value !== valor) this.value = valor;
        });
    }

    function filtrarSoloDigitos(input) {
        if (!input) return;
        input.addEventListener('input', function () {
            var valor = this.value.replace(/\D/g, '');
            if (this.value !== valor) this.value = valor;
        });
    }

    function normalizarPrefijo(valor) {
        var limpio = (valor || '').trim().replace(/[^\d+]/g, '');
        if (limpio === '') return '+591';
        if (limpio.charAt(0) !== '+') limpio = '+' + limpio.replace(/\+/g, '');
        return limpio;
    }

    function sincronizarTelefonoHidden() {
        if (!telefonoHidden) return;
        var prefijo = normalizarPrefijo(telefonoPrefijo ? telefonoPrefijo.value : '+591');
        var numero = telefonoNumero ? telefonoNumero.value.replace(/\D/g, '') : '';
        if (telefonoNumero && telefonoNumero.value !== numero) telefonoNumero.value = numero;
        telefonoHidden.value = numero ? (prefijo + ' ' + numero) : prefijo;
    }

    function aplicarPrefijoDesdePais() {
        if (!telefonoPais || !telefonoPrefijo) return;
        telefonoPrefijo.value = telefonoPais.value;
        sincronizarTelefonoHidden();
    }

    function sincronizarPaisDesdePrefijo() {
        if (!telefonoPais || !telefonoPrefijo) return;
        var prefijo = normalizarPrefijo(telefonoPrefijo.value);
        telefonoPrefijo.value = prefijo;
        var opcion = Array.prototype.find.call(telefonoPais.options, function (opt) {
            return opt.value === prefijo;
        });
        if (opcion) telefonoPais.value = prefijo;
        sincronizarTelefonoHidden();
    }

    function abrirSelectorPais() {
        if (!telefonoPais) return;
        if (typeof telefonoPais.showPicker === 'function') {
            try { telefonoPais.showPicker(); return; } catch (e) { /* fallback */ }
        }
        telefonoPais.focus();
    }

    radios.forEach(function (r) { r.addEventListener('change', actualizar); });
    if (telefonoPaisBtn) telefonoPaisBtn.addEventListener('click', abrirSelectorPais);
    if (telefonoPais) telefonoPais.addEventListener('change', aplicarPrefijoDesdePais);
    if (telefonoPrefijo) {
        telefonoPrefijo.addEventListener('input', function () {
            var v = telefonoPrefijo.value.replace(/[^\d+]/g, '');
            if (v.indexOf('+') > 0) v = v.replace(/\+/g, '');
            if (v && v.charAt(0) !== '+') v = '+' + v.replace(/\+/g, '');
            if (telefonoPrefijo.value !== v) telefonoPrefijo.value = v;
            sincronizarPaisDesdePrefijo();
        });
        telefonoPrefijo.addEventListener('blur', sincronizarPaisDesdePrefijo);
    }
    if (telefonoNumero) {
        filtrarSoloDigitos(telefonoNumero);
        telefonoNumero.addEventListener('input', sincronizarTelefonoHidden);
    }
    filtrarSoloLetras(document.getElementById('nombre'));
    filtrarSoloLetras(document.getElementById('apellido'));
    filtrarAlfanumerico(document.getElementById('ci_nit'));
    if (form) form.addEventListener('submit', sincronizarTelefonoHidden);

    actualizar();
    sincronizarPaisDesdePrefijo();
})();
</script>
@endpush
