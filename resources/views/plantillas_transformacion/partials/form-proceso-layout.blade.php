@push('styles')
<style>
.pt-form-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 55%, #3b82f6 100%);
    color: #fff;
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
.pt-form-hero h2 { font-size: 1.15rem; font-weight: 700; margin: 0 0 .35rem; }
.pt-form-hero p { margin: 0; opacity: .92; font-size: .9rem; }
.pt-linea-wrap {
    background: linear-gradient(180deg, #f0f7f1 0%, #fff 100%);
    border: 1px solid #dce9de;
    border-radius: 14px;
    padding: 1.15rem 1.25rem;
}
</style>
@endpush

<div class="pt-form-hero">
    <h2><i class="fas fa-project-diagram mr-2"></i>{{ $tituloHero ?? 'Proceso de transformación' }}</h2>
    <p>{{ $subtituloHero ?? 'Defina el nombre, la descripción y ordene las etapas de la línea. El último paso siempre es Empaquetado.' }}</p>
</div>

<div class="row mb-3">
    <div class="col-md-6 form-group">
        <label class="font-weight-bold">Nombre del proceso <span class="text-danger">*</span></label>
        <input name="nombre" class="form-control form-control-lg" value="{{ old('nombre', $nombreValor ?? '') }}" required maxlength="120"
               placeholder="Ej. Línea de papas procesadas">
    </div>
    <div class="col-md-6 form-group">
        <label class="font-weight-bold">Descripción <span class="text-muted font-weight-normal">(opcional)</span></label>
        <input name="descripcion" class="form-control" value="{{ old('descripcion', $descripcionValor ?? '') }}"
               placeholder="Ej. Pelado, cocción y empaque para producto fresco">
    </div>
</div>

<div class="pt-linea-wrap">
    @include('plantillas_transformacion.partials.form-pasos', ['pasosIniciales' => $pasosIniciales ?? old('pasos', [])])
</div>
