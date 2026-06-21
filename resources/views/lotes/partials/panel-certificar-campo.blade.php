<div class="cert-campo-panel mb-3" id="panel-certificacion-campo">
    <div class="cert-campo-panel__header">
        <div class="cert-campo-panel__header-icon">
            <i class="fas fa-certificate"></i>
        </div>
        <div>
            <h6 class="cert-campo-panel__title mb-1">Certificación del lote</h6>
            <p class="cert-campo-panel__subtitle mb-0">
                Evalúe la cosecha. Solo los lotes <strong>certificados</strong> pueden enviarse al almacén agrícola.
            </p>
        </div>
    </div>

  @can('certificaciones.create')
    <div class="cert-campo-panel__body">
        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="cert-campo-opcion cert-campo-opcion--ok">
                    <div class="cert-campo-opcion__head">
                        <span class="cert-campo-opcion__badge cert-campo-opcion__badge--ok">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div>
                            <h6 class="cert-campo-opcion__title">Certificado</h6>
                            <p class="cert-campo-opcion__hint mb-0">Cumple calidad y condiciones para almacén.</p>
                        </div>
                    </div>
                    <form action="{{ route('certificaciones.store') }}" method="POST" class="cert-campo-opcion__form">
                        @csrf
                        <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                        <input type="hidden" name="resultado" value="{{ \App\Models\CertificacionLote::RAZON_CERTIFICADO }}">
                        <input type="hidden" name="return" value="{{ route('lotes.trazabilidad', $lote, absolute: false) }}">
                        <input type="hidden" name="from_trazabilidad" value="1">
                        <label class="cert-campo-opcion__label" for="cert-obs-ok">Observaciones (opcional)</label>
                        <textarea id="cert-obs-ok" name="observaciones" class="form-control cert-campo-opcion__input" rows="2"
                                  maxlength="1000" placeholder="Calidad, condición del producto, humedad, presentación…">{{ old('resultado') === \App\Models\CertificacionLote::RAZON_CERTIFICADO ? old('observaciones') : '' }}</textarea>
                        <button type="submit" class="btn btn-success btn-block cert-campo-opcion__btn mt-3">
                            <i class="fas fa-stamp mr-1"></i> Certificar lote
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="cert-campo-opcion cert-campo-opcion--no">
                    <div class="cert-campo-opcion__head">
                        <span class="cert-campo-opcion__badge cert-campo-opcion__badge--no">
                            <i class="fas fa-times-circle"></i>
                        </span>
                        <div>
                            <h6 class="cert-campo-opcion__title">No conforme</h6>
                            <p class="cert-campo-opcion__hint mb-0">No cumple estándares; no podrá ir al almacén.</p>
                        </div>
                    </div>
                    <form action="{{ route('certificaciones.store') }}" method="POST" class="cert-campo-opcion__form"
                          onsubmit="return confirm('¿Marcar este lote como No conforme? No podrá enviarse al almacén.');">
                        @csrf
                        <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                        <input type="hidden" name="resultado" value="{{ \App\Models\CertificacionLote::RAZON_NO_CONFORME }}">
                        <input type="hidden" name="return" value="{{ route('lotes.trazabilidad', $lote, absolute: false) }}">
                        <input type="hidden" name="from_trazabilidad" value="1">
                        <label class="cert-campo-opcion__label" for="cert-motivo-no">Motivo <span class="text-danger">*</span></label>
                        <input id="cert-motivo-no" type="text" name="observaciones" class="form-control cert-campo-opcion__input mb-2" required maxlength="1000"
                               value="{{ old('resultado') === \App\Models\CertificacionLote::RAZON_NO_CONFORME ? old('observaciones') : '' }}"
                               placeholder="Daños, plagas, humedad, calidad deficiente…">
                        <label class="cert-campo-opcion__label" for="cert-recom-no">Recomendaciones para mejorar</label>
                        <input id="cert-recom-no" type="text" name="recomendaciones" class="form-control cert-campo-opcion__input"
                               value="{{ old('recomendaciones') }}" maxlength="2000"
                               placeholder="Secado, selección, tratamiento, etc.">
                        <button type="submit" class="btn btn-outline-danger btn-block cert-campo-opcion__btn mt-3">
                            <i class="fas fa-ban mr-1"></i> Marcar como no conforme
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
  @else
    <div class="cert-campo-panel__body">
        <div class="alert alert-light border small mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Solicite a un responsable con permiso de certificación que evalúe este lote.
        </div>
    </div>
  @endcan
</div>
