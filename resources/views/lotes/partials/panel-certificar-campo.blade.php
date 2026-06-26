<div class="card lote-section-card mb-3 border-0 shadow-sm" id="panel-certificacion-campo" style="border-radius:14px;overflow:hidden">
    <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-certificate mr-2" style="color:#7c3aed"></i>Certificación</span>
    </div>
    <div class="card-body">
        @can('certificaciones.create')
            @if(\App\Support\UsuarioRol::gestionaCampo(auth()->user()))
            <p class="small text-muted mb-3">
                Registre el resultado del control de calidad. Solo los lotes <strong>conformes</strong> pueden pasar a almacenaje.
            </p>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <form action="{{ route('certificaciones.store') }}" method="POST" class="border rounded p-3 h-100" style="background:#f0fdf4;border-color:#bbf7d0!important">
                        @csrf
                        <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                        <input type="hidden" name="resultado" value="{{ \App\Models\CertificacionLote::RAZON_CERTIFICADO }}">
                        <input type="hidden" name="return" value="{{ route('lotes.trazabilidad', $lote, absolute: false) }}">
                        <input type="hidden" name="from_trazabilidad" value="1">
                        <h6 class="font-weight-bold text-success mb-2"><i class="fas fa-check-circle mr-1"></i>Conforme</h6>
                        <input type="text" name="observaciones" class="form-control form-control-sm mb-2" maxlength="1000"
                               value="{{ old('observaciones') }}" placeholder="Observación (opcional)">
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                            <i class="fas fa-stamp mr-1"></i>Registrar conforme
                        </button>
                    </form>
                </div>
                <div class="col-lg-6 mb-3">
                    <form action="{{ route('certificaciones.store') }}" method="POST" class="border rounded p-3 h-100" style="background:#fffbeb;border-color:#fde68a!important"
                          onsubmit="return confirm('¿Marcar este lote como no conforme? No podrá enviarse al almacén.');">
                        @csrf
                        <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                        <input type="hidden" name="resultado" value="{{ \App\Models\CertificacionLote::RAZON_NO_CONFORME }}">
                        <input type="hidden" name="return" value="{{ route('lotes.trazabilidad', $lote, absolute: false) }}">
                        <input type="hidden" name="from_trazabilidad" value="1">
                        <h6 class="font-weight-bold text-warning mb-2"><i class="fas fa-times-circle mr-1"></i>No conforme</h6>
                        <input type="text" name="observaciones" class="form-control form-control-sm mb-2" maxlength="1000" required
                               placeholder="Motivo obligatorio: daños, calidad…">
                        <input type="text" name="recomendaciones" class="form-control form-control-sm mb-2" maxlength="2000"
                               placeholder="Recomendaciones (opcional)">
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold text-dark">
                            <i class="fas fa-ban mr-1"></i>Registrar no conforme
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-light border small mb-0">
                <i class="fas fa-info-circle mr-1 text-muted"></i>
                Solo el jefe agrícola o administración debe registrar la certificación (conforme o no conforme).
            </div>
            @endif
        @else
            <div class="alert alert-light border small mb-0">
                <i class="fas fa-info-circle mr-1 text-muted"></i>
                Solicite a un responsable con permiso de certificación que evalúe este lote.
            </div>
        @endcan
    </div>
</div>
