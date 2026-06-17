<div class="card lote-section-card mb-3 border" id="panel-certificacion-campo" style="border-color:#c4b5fd !important;">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 font-weight-bold" style="color:#7c3aed">
            <i class="fas fa-certificate mr-2"></i>Certificación del lote
        </h6>
    </div>
    <div class="card-body">
        <p class="small text-muted mb-3">
            Evalúe la cosecha como <strong>Certificado</strong> o <strong>No conforme</strong>.
            Solo los lotes certificados pueden enviarse al almacén agrícola.
        </p>

        @can('certificaciones.create')
            <form action="{{ route('certificaciones.store') }}" method="POST" class="mb-3">
                @csrf
                <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                <input type="hidden" name="resultado" value="{{ \App\Models\CertificacionLote::RAZON_CERTIFICADO }}">
                <input type="hidden" name="return" value="{{ route('lotes.trazabilidad', $lote, absolute: false) }}">
                <input type="hidden" name="from_trazabilidad" value="1">
                <div class="form-row align-items-end">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted">Observaciones (opcional)</label>
                        <input type="text" name="observaciones" class="form-control form-control-sm"
                               value="{{ old('observaciones') }}" maxlength="1000"
                               placeholder="Calidad, condición del producto, etc.">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold">
                            <i class="fas fa-stamp mr-1"></i> Certificar lote
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{ route('certificaciones.store') }}" method="POST"
                  onsubmit="return confirm('¿Marcar este lote como No conforme? No podrá enviarse al almacén.');">
                @csrf
                <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                <input type="hidden" name="resultado" value="{{ \App\Models\CertificacionLote::RAZON_NO_CONFORME }}">
                <input type="hidden" name="return" value="{{ route('lotes.trazabilidad', $lote, absolute: false) }}">
                <input type="hidden" name="from_trazabilidad" value="1">
                <div class="form-row align-items-end">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted">Motivo no conforme <span class="text-danger">*</span></label>
                        <input type="text" name="observaciones" class="form-control form-control-sm" required maxlength="1000"
                               placeholder="Daños, plagas, humedad, calidad deficiente…">
                    </div>
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-muted">Recomendaciones para mejorar</label>
                        <input type="text" name="recomendaciones" class="form-control form-control-sm" maxlength="2000"
                               placeholder="Secado, selección, tratamiento, etc.">
                    </div>
                    <div class="col-md-12 mt-2">
                        <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold">
                            <i class="fas fa-times-circle mr-1"></i> No conforme
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-light border small mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Solicite a un responsable con permiso de certificación que evalúe este lote.
            </div>
        @endcan
    </div>
</div>
