@php
    $variablesCatalogo = $variablesCatalogo ?? \App\Models\VariableEstandar::where('activo', true)->orderBy('nombre')->get();
    $variablesIniciales = $variablesIniciales ?? [];
    if (empty($variablesIniciales) && isset($maquina)) {
        $variablesIniciales = $maquina->variablesSugeridas()
            ->with('variableEstandar')
            ->get()
            ->map(fn ($v) => [
                'variableestandarid' => $v->variableestandarid,
                'valor_minimo' => $v->valor_minimo,
                'valor_maximo' => $v->valor_maximo,
                'obligatorio' => $v->obligatorio,
            ])->all();
    }
    if (is_array(old('variables_sugeridas'))) {
        $variablesIniciales = array_values(old('variables_sugeridas'));
    }
@endphp

<div class="maq-vars-panel mt-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2" style="gap:.5rem">
        <div>
            <h6 class="font-weight-bold text-success mb-1"><i class="fas fa-sliders-h mr-1"></i> Parámetros sugeridos</h6>
            <p class="small text-muted mb-0">
                Rangos típicos de esta máquina. Al usarla en un proceso de transformación se precargan automáticamente (puede ajustarlos por paso).
            </p>
        </div>
        <button type="button" class="btn btn-sm btn-success" id="btnAgregarVarMaquina">
            <i class="fas fa-plus mr-1"></i> Agregar parámetro
        </button>
    </div>
    <div id="listaVarsMaquina"></div>
    <div id="varsMaquinaVacio" class="text-center py-3 text-muted small border rounded bg-white">
        <i class="fas fa-thermometer-half fa-lg mb-2 d-block opacity-40"></i>
        Sin parámetros aún. Agregue temperatura, presión, humedad u otros según el equipo.
    </div>
</div>

<template id="tplVarMaquina">
    <div class="maq-var-card" data-var-id="">
        <div class="maq-var-card__head">
            <label>Parámetro</label>
            <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-var py-0 px-2" title="Quitar"><i class="fas fa-times"></i></button>
        </div>
        <select class="form-control form-control-sm var-select mb-2" required>
            <option value="">Seleccionar variable…</option>
            @foreach($variablesCatalogo as $var)
                <option value="{{ $var->variableestandarid }}" data-unidad="{{ $var->unidad }}">
                    {{ $var->nombre }}@if($var->unidad) · {{ $var->unidad }}@endif
                </option>
            @endforeach
        </select>
        <div class="maq-var-rango">
            <div>
                <label>Mínimo permitido</label>
                <input type="number" step="0.01" class="form-control form-control-sm var-min" required placeholder="Ej. 10">
            </div>
            <div>
                <label>Máximo permitido</label>
                <input type="number" step="0.01" class="form-control form-control-sm var-max" required placeholder="Ej. 100">
            </div>
        </div>
        <input type="hidden" class="var-oblig-hidden" value="1">
        <p class="small text-muted mb-0 mt-2"><i class="fas fa-info-circle mr-1"></i>Todos los parámetros son obligatorios al ejecutar el paso en planta.</p>
    </div>
</template>

@push('scripts')
<script>
(function () {
    const lista = document.getElementById('listaVarsMaquina');
    const tpl = document.getElementById('tplVarMaquina');
    const vacio = document.getElementById('varsMaquinaVacio');
    const btnAgregar = document.getElementById('btnAgregarVarMaquina');
    const iniciales = @json($variablesIniciales);
    if (!lista || !tpl) return;

    let uid = 0;

    function filas() { return lista.querySelectorAll('.maq-var-card'); }

    function actualizarVacio() {
        if (vacio) vacio.classList.toggle('d-none', filas().length > 0);
    }

    function renumerarNombres() {
        filas().forEach((row, idx) => {
            const id = row.dataset.varId;
            row.querySelector('.var-select').name = 'variables_sugeridas[' + idx + '][variableestandarid]';
            row.querySelector('.var-min').name = 'variables_sugeridas[' + idx + '][valor_minimo]';
            row.querySelector('.var-max').name = 'variables_sugeridas[' + idx + '][valor_maximo]';
            const obl = row.querySelector('.var-oblig-hidden');
            if (obl) obl.name = 'variables_sugeridas[' + idx + '][obligatorio]';
        });
        actualizarVacio();
    }

    function agregarFila(data) {
        const node = tpl.content.cloneNode(true);
        const row = node.querySelector('.maq-var-card');
        const varId = ++uid;
        row.dataset.varId = String(varId);
        if (data?.variableestandarid) row.querySelector('.var-select').value = String(data.variableestandarid);
        if (data?.valor_minimo != null) row.querySelector('.var-min').value = data.valor_minimo;
        if (data?.valor_maximo != null) row.querySelector('.var-max').value = data.valor_maximo;
        lista.appendChild(node);
        renumerarNombres();
    }

    btnAgregar?.addEventListener('click', () => agregarFila(null));
    lista.addEventListener('click', e => {
        if (e.target.closest('.btn-quitar-var')) {
            e.target.closest('.maq-var-card')?.remove();
            renumerarNombres();
        }
    });
    (iniciales.length ? iniciales : []).forEach(agregarFila);
    actualizarVacio();
})();
</script>
@endpush
