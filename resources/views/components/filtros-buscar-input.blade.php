@props([
    'name' => 'q',
    'value' => '',
    'placeholder' => 'Buscar…',
    'label' => 'Buscar',
])

<div class="col-lg-4 col-md-6 mb-2">
    <label class="small text-muted mb-1">{{ $label }}</label>
    <div class="input-group input-group-sm">
        <div class="input-group-prepend">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
        </div>
        <input type="text" name="{{ $name }}" class="form-control"
            value="{{ $value }}" placeholder="{{ $placeholder }}">
    </div>
</div>
