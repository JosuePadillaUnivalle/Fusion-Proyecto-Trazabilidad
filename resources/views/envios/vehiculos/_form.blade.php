@php
    use App\Support\VehiculoTransporteCatalogo;
    use App\Support\TransportistaFlotaCatalogo;

    $v = $vehiculo ?? null;
    $tipoIdForm = old('tipovehiculoid', $v?->tipovehiculoid);
    $tipoForm = $tipoIdForm ? $tipos->firstWhere('tipovehiculoid', (int) $tipoIdForm) : null;

    $valorEfectivo = function (string $campoOverride, string $campoTipo) use ($v, $tipoForm) {
        $desdeOverride = old($campoOverride, $v?->{$campoOverride});
        if (filled($desdeOverride)) {
            return $desdeOverride;
        }

        return $tipoForm?->{$campoTipo} ?? '';
    };

    $transporteAsignado = collect();
    if ($v?->relationLoaded('tiposTransporte') || $v?->tiposTransporte) {
        $transporteAsignado = $v->tiposTransporte ?? collect();
    }
    if ($transporteAsignado->isEmpty() && $tipoForm?->tiposTransporte) {
        $transporteAsignado = $tipoForm->tiposTransporte;
    }

    $transporteSeleccionado = old('tipotransporteid', VehiculoTransporteCatalogo::idPrincipalDesdeColeccion($transporteAsignado));

    $tiposJson = $tipos->map(fn ($t) => [
        'id' => $t->tipovehiculoid,
        'kg' => $t->capacidad_kg,
        'm3' => $t->capacidad_m3,
        'largo' => $t->largo_m,
        'ancho' => $t->ancho_m,
        'alto' => $t->alto_m,
        'licencia' => $t->licencia_requerida,
        'codigo' => $t->codigo,
        'transporte_id' => VehiculoTransporteCatalogo::idPrincipalDesdeColeccion($t->tiposTransporte ?? collect()),
    ])->values();

    $placaPreview = strtoupper((string) old('placa', $v?->placa ?? 'NUEVA UNIDAD'));
    $marcaPreview = trim((string) old('marca', $v?->marca ?? ''));
    $modeloPreview = trim((string) old('modelo', $v?->modelo ?? ''));
    $vehiculoPreview = trim($marcaPreview.' '.$modeloPreview) ?: 'Complete los datos del vehículo';
    $ambitoPreview = TransportistaFlotaCatalogo::categoriaCorta(old('ambito_flota', $v?->ambito_flota ?? 'agricola'));
@endphp

@push('styles')
    @include('envios.vehiculos.partials.form-styles')
@endpush

<div class="page-veh-form">
    <div class="veh-form-shell">
        <div class="veh-form-hero">
            <div>
                <div class="veh-form-hero__placa" id="vehPlacaPreview">{{ $placaPreview }}</div>
                <div class="veh-form-hero__meta" id="vehModeloPreview">{{ $vehiculoPreview }}</div>
            </div>
            <div class="veh-form-hero__badge" id="vehAmbitoPreview">{{ $ambitoPreview }}</div>
        </div>

        {{-- Identificación --}}
        <section class="veh-form-section">
            <div class="veh-form-section__head">
                <span class="veh-form-section__icon veh-form-section__icon--ident"><i class="fas fa-id-card"></i></span>
                <div>
                    <h4 class="veh-form-section__title">Identificación</h4>
                    <p class="veh-form-section__sub">Datos de placa y descripción de la unidad</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group veh-field veh-field--placa">
                        <label>Placa <span class="text-danger">*</span></label>
                        <input name="placa" id="vehPlacaInput" class="form-control text-uppercase" value="{{ old('placa', $v?->placa) }}" required maxlength="20" placeholder="Ej. 902391KLS">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group veh-field">
                        <label>Marca</label>
                        <input name="marca" id="vehMarcaInput" class="form-control" value="{{ old('marca', $v?->marca) }}" placeholder="Ej. Ford">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group veh-field">
                        <label>Modelo</label>
                        <input name="modelo" id="vehModeloInput" class="form-control" value="{{ old('modelo', $v?->modelo) }}" placeholder="Ej. Ranger">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group veh-field">
                        <label>Año</label>
                        <input type="number" name="anio" class="form-control" value="{{ old('anio', $v?->anio) }}" min="1980" max="2100" placeholder="2024">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group veh-field">
                        <label>Color</label>
                        <input name="color" class="form-control" value="{{ old('color', $v?->color) }}" placeholder="Ej. Gris">
                    </div>
                </div>
            </div>
        </section>

        {{-- Clasificación --}}
        <section class="veh-form-section">
            <div class="veh-form-section__head">
                <span class="veh-form-section__icon veh-form-section__icon--class"><i class="fas fa-truck"></i></span>
                <div>
                    <h4 class="veh-form-section__title">Clasificación en flota</h4>
                    <p class="veh-form-section__sub">Tipo de vehículo del catálogo y ámbito operativo</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-7">
                    <div class="form-group veh-field mb-md-0">
                        <label>Tipo de vehículo <span class="text-danger">*</span></label>
                        <select name="tipovehiculoid" id="tipovehiculoid" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($tipos as $tipo)
                                @php
                                    $capKg = $tipo->capacidad_kg ? number_format((float) $tipo->capacidad_kg, 0).' kg' : null;
                                    $capM3 = $tipo->capacidad_m3 ? number_format((float) $tipo->capacidad_m3, 1).' m³' : null;
                                    $capTxt = collect([$capKg, $capM3])->filter()->implode(' / ');
                                    $lic = $tipo->licencia_requerida ? 'Lic. '.$tipo->licencia_requerida : null;
                                    $optionLabel = $tipo->nombre;
                                    if ($capTxt) {
                                        $optionLabel .= ' — '.$capTxt;
                                    }
                                    if ($lic) {
                                        $optionLabel .= ' · '.$lic;
                                    }
                                @endphp
                                <option value="{{ $tipo->tipovehiculoid }}" @selected((string) old('tipovehiculoid', $v?->tipovehiculoid) === (string) $tipo->tipovehiculoid)>
                                    {{ $optionLabel }}
                                </option>
                            @endforeach
                        </select>
                        <div id="tipoVehiculoResumen" class="veh-tipo-resumen" style="display:none;"></div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group veh-field mb-0">
                        <label>Categoría <span class="text-danger">*</span></label>
                        <select name="ambito_flota" id="vehAmbitoInput" class="form-control" required>
                            @foreach(TransportistaFlotaCatalogo::etiquetas() as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('ambito_flota', $v?->ambito_flota ?? 'agricola') === $valor)>
                                    {{ TransportistaFlotaCatalogo::categoriaCorta($valor) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </section>

        {{-- Equipamiento térmico --}}
        <section class="veh-form-section">
            <div class="veh-form-section__head">
                <span class="veh-form-section__icon veh-form-section__icon--thermal"><i class="fas fa-shipping-fast"></i></span>
                <div>
                    <h4 class="veh-form-section__title">Equipamiento de transporte</h4>
                    <p class="veh-form-section__sub">Seleccione <strong>un solo</strong> modo según el equipamiento real de la unidad</p>
                </div>
            </div>
            @error('tipotransporteid')
                <div class="alert alert-danger py-2 small mb-3">{{ $message }}</div>
            @enderror
            @if(($catalogoTransporte ?? collect())->isEmpty())
                <p class="text-muted small mb-0">No hay tipos de transporte en el catálogo.</p>
            @else
                <div class="veh-transporte-grid" id="tiposTransporteRadios">
                    @foreach($catalogoTransporte as $tt)
                        @php $meta = VehiculoTransporteCatalogo::metaUi($tt->codigo); @endphp
                        <label class="veh-transporte-card js-transporte-card @if((int) $transporteSeleccionado === (int) $tt->tipotransporteid) is-selected @endif">
                            <input type="radio"
                                   class="js-tipo-transporte"
                                   name="tipotransporteid"
                                   value="{{ $tt->tipotransporteid }}"
                                   @checked((int) $transporteSeleccionado === (int) $tt->tipotransporteid)
                                   required>
                            <span class="veh-transporte-card__check"><i class="fas fa-check"></i></span>
                            <span class="veh-transporte-card__icon tone-{{ $meta['tone'] }}"><i class="fas {{ $meta['icon'] }}"></i></span>
                            <div class="veh-transporte-card__name">{{ $tt->nombre }}</div>
                            <p class="veh-transporte-card__desc">{{ $tt->descripcion ?: $meta['hint'] }}</p>
                        </label>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Capacidad --}}
        <section class="veh-form-section">
            <div class="veh-form-section__head">
                <span class="veh-form-section__icon veh-form-section__icon--capacity"><i class="fas fa-weight-hanging"></i></span>
                <div>
                    <h4 class="veh-form-section__title">Capacidad de carga</h4>
                    <p class="veh-form-section__sub">Se completan desde el tipo; edite solo si esta unidad difiere del estándar</p>
                </div>
            </div>
            <div class="veh-capacity-grid">
                <div class="veh-capacity-box veh-field">
                    <label><i class="fas fa-weight-hanging text-muted"></i> Peso máximo (kg)</label>
                    <input type="number" step="0.01" min="0" name="capacidad_kg_override" id="capacidad_kg_override" class="form-control"
                           value="{{ $valorEfectivo('capacidad_kg_override', 'capacidad_kg') }}" placeholder="Ej. 3500">
                </div>
                <div class="veh-capacity-box veh-field">
                    <label><i class="fas fa-cube text-muted"></i> Volumen máximo (m³)</label>
                    <input type="number" step="0.01" min="0" name="capacidad_m3_override" id="capacidad_m3_override" class="form-control"
                           value="{{ $valorEfectivo('capacidad_m3_override', 'capacidad_m3') }}" placeholder="Ej. 15">
                </div>
            </div>
        </section>

        {{-- Dimensiones --}}
        <section class="veh-form-section">
            <div class="veh-form-section__head">
                <span class="veh-form-section__icon veh-form-section__icon--dims"><i class="fas fa-ruler-combined"></i></span>
                <div>
                    <h4 class="veh-form-section__title">Dimensiones de caja</h4>
                    <p class="veh-form-section__sub">Largo, ancho y alto en metros para estimar volumen útil</p>
                </div>
            </div>
            <div class="veh-dims-grid">
                <div class="veh-field">
                    <label>Largo (m)</label>
                    <input type="number" step="0.01" min="0" name="largo_m_override" id="largo_m_override" class="form-control"
                           value="{{ $valorEfectivo('largo_m_override', 'largo_m') }}" placeholder="Ej. 4.20">
                </div>
                <div class="veh-field">
                    <label>Ancho (m)</label>
                    <input type="number" step="0.01" min="0" name="ancho_m_override" id="ancho_m_override" class="form-control"
                           value="{{ $valorEfectivo('ancho_m_override', 'ancho_m') }}" placeholder="Ej. 2.10">
                </div>
                <div class="veh-field">
                    <label>Alto (m)</label>
                    <input type="number" step="0.01" min="0" name="alto_m_override" id="alto_m_override" class="form-control"
                           value="{{ $valorEfectivo('alto_m_override', 'alto_m') }}" placeholder="Ej. 2.00">
                </div>
            </div>
        </section>

        <div class="veh-form-footer">
            <label class="veh-toggle-activo">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" id="activoVehiculo" name="activo" value="1" @checked(old('activo', $v?->activo ?? true))>
                <span class="veh-toggle-track"></span>
                <span class="veh-toggle-label">Vehículo activo en flota</span>
            </label>
            @isset($showFormActions)
                <div class="veh-form-actions">
                    {!! $showFormActions !!}
                </div>
            @endisset
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tipos = @json($tiposJson);
    const select = document.getElementById('tipovehiculoid');
    const resumen = document.getElementById('tipoVehiculoResumen');
    const placaInput = document.getElementById('vehPlacaInput');
    const marcaInput = document.getElementById('vehMarcaInput');
    const modeloInput = document.getElementById('vehModeloInput');
    const ambitoInput = document.getElementById('vehAmbitoInput');
    const placaPreview = document.getElementById('vehPlacaPreview');
    const modeloPreview = document.getElementById('vehModeloPreview');
    const ambitoPreview = document.getElementById('vehAmbitoPreview');

    const inputs = {
        kg: document.getElementById('capacidad_kg_override'),
        m3: document.getElementById('capacidad_m3_override'),
        largo: document.getElementById('largo_m_override'),
        ancho: document.getElementById('ancho_m_override'),
        alto: document.getElementById('alto_m_override'),
    };

    const radiosTransporte = Array.from(document.querySelectorAll('.js-tipo-transporte'));
    const cardsTransporte = Array.from(document.querySelectorAll('.js-transporte-card'));

    function marcarTransporte(id) {
        if (!id) return;
        radiosTransporte.forEach(radio => {
            radio.checked = String(radio.value) === String(id);
        });
        cardsTransporte.forEach(card => {
            const radio = card.querySelector('.js-tipo-transporte');
            card.classList.toggle('is-selected', radio && radio.checked);
        });
    }

    cardsTransporte.forEach(card => {
        card.addEventListener('click', function () {
            const radio = card.querySelector('.js-tipo-transporte');
            if (radio) {
                radio.checked = true;
                marcarTransporte(radio.value);
            }
        });
    });

    function tipoActual() {
        return tipos.find(t => String(t.id) === String(select?.value || '')) || null;
    }

    function asignar(inp, valor) {
        if (!inp) return;
        inp.value = (valor !== null && valor !== '' && Number(valor) > 0) ? valor : '';
    }

    function rellenarDesdeTipo(tipo) {
        if (!tipo) {
            Object.values(inputs).forEach(inp => { if (inp) inp.value = ''; });
            return;
        }
        asignar(inputs.kg, tipo.kg);
        asignar(inputs.m3, tipo.m3);
        asignar(inputs.largo, tipo.largo);
        asignar(inputs.ancho, tipo.ancho);
        asignar(inputs.alto, tipo.alto);
    }

    function pintarResumen() {
        if (!resumen) return;
        const tipo = tipoActual();
        if (!tipo) {
            resumen.style.display = 'none';
            resumen.textContent = '';
            return;
        }
        const partes = [];
        if (tipo.kg) partes.push(Number(tipo.kg).toLocaleString('es-BO') + ' kg');
        if (tipo.m3) partes.push(Number(tipo.m3).toLocaleString('es-BO') + ' m³');
        if (tipo.licencia) partes.push('Licencia mínima ' + tipo.licencia);
        if (partes.length) {
            resumen.style.display = 'block';
            resumen.textContent = 'Referencia del catálogo: ' + partes.join(' · ');
        } else {
            resumen.style.display = 'none';
        }
    }

    function pintarHero() {
        if (placaPreview && placaInput) {
            placaPreview.textContent = (placaInput.value || 'NUEVA UNIDAD').toUpperCase();
        }
        if (modeloPreview && marcaInput && modeloInput) {
            const txt = [marcaInput.value, modeloInput.value].map(s => (s || '').trim()).filter(Boolean).join(' ');
            modeloPreview.textContent = txt || 'Complete los datos del vehículo';
        }
        if (ambitoPreview && ambitoInput) {
            const opt = ambitoInput.options[ambitoInput.selectedIndex];
            ambitoPreview.textContent = opt ? opt.textContent.trim() : '';
        }
    }

    [placaInput, marcaInput, modeloInput, ambitoInput].forEach(el => {
        if (!el) return;
        el.addEventListener('input', pintarHero);
        el.addEventListener('change', pintarHero);
    });

    if (select) {
        select.addEventListener('change', function () {
            const tipo = tipoActual();
            rellenarDesdeTipo(tipo);
            if (tipo && tipo.transporte_id) {
                marcarTransporte(tipo.transporte_id);
            }
            pintarResumen();
        });
    }

    pintarResumen();
    pintarHero();

    const tipo = tipoActual();
    if (tipo) {
        const sinDatos = !Object.values(inputs).some(inp => inp && String(inp.value || '').trim() !== '');
        if (sinDatos) {
            rellenarDesdeTipo(tipo);
        }
    }
})();
</script>
@endpush
