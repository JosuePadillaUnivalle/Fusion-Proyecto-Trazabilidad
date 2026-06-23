<div id="panelParametrosLote" class="lote-section d-none mt-3 mb-0">
    <div class="lote-section-title"><i class="fas fa-sliders-h mr-1"></i> Parámetros del proceso <span class="text-muted font-weight-normal">(opcional)</span></div>
    <p class="small text-muted mb-2">Por defecto se usan los rangos del proceso de transformación. Puede ajustarlos para este lote sin superar el límite de la máquina.</p>
    <div class="custom-control custom-checkbox mb-2">
        <input type="checkbox" class="custom-control-input" id="chkPersonalizarParametros" name="personalizar_parametros" value="1" @checked(old('personalizar_parametros'))>
        <label class="custom-control-label small font-weight-bold" for="chkPersonalizarParametros">Personalizar parámetros para este lote</label>
    </div>
    <div id="parametrosLoteLista" class="d-none"></div>
    <p id="parametrosLoteVacio" class="small text-muted mb-0">Seleccione un proceso de transformación para ver los parámetros.</p>
</div>

@push('styles')
<style>
.lote-param-paso { border: 1px solid #e2ebe3; border-radius: 10px; background: #fff; margin-bottom: .5rem; overflow: hidden; }
.lote-param-paso__head { padding: .5rem .75rem; background: #f0f7f1; font-size: .82rem; font-weight: 700; color: #2c5530; }
.lote-param-var { display: grid; grid-template-columns: 1.2fr .65fr .65fr; gap: .35rem; align-items: center; padding: .4rem .75rem; border-top: 1px dashed #e8efe9; font-size: .8rem; }
.lote-param-var:first-of-type { border-top: 0; }
.lote-param-var input[type="number"] { font-size: .8rem; }
.lote-param-var.is-readonly input { background: #f8faf8; pointer-events: none; }
.lote-param-limite { font-size: .7rem; color: #64748b; }
</style>
@endpush
