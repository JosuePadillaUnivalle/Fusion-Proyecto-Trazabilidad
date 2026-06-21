@php
    $inputId = $inputId ?? 'evidencia_foto';
    $inputName = $inputName ?? 'evidencia_foto';
    $btnLabel = $btnLabel ?? 'Elegir imagen';
    $required = !empty($required);
@endphp

<div class="upload-foto" data-upload-wrap="{{ $inputId }}">
    <input type="file"
           name="{{ $inputName }}"
           id="{{ $inputId }}"
           class="upload-foto__input d-none"
           accept="image/jpeg,image/jpg,image/png,image/webp"
           @if($required) required @endif>
    <div class="upload-foto__panel">
        <button type="button" class="upload-foto__btn" data-upload-trigger="{{ $inputId }}">
            <i class="fas fa-cloud-upload-alt"></i>
            <span>{{ $btnLabel }}</span>
        </button>
        <span class="upload-foto__nombre" data-upload-label="{{ $inputId }}">Ningún archivo seleccionado</span>
    </div>
    <div class="upload-foto__preview text-center mt-3" data-upload-preview="{{ $inputId }}" style="display: none;">
        <img data-upload-img="{{ $inputId }}" alt="Vista previa" class="upload-foto__img">
    </div>
</div>

@once
@push('styles')
<style>
    .upload-foto__panel {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .75rem 1rem;
        padding: .85rem 1rem;
        background: #f8fbf8;
        border: 2px dashed #c5d9c8;
        border-radius: 10px;
        transition: border-color .2s, background .2s;
    }
    .upload-foto__panel:hover,
    .upload-foto__panel.is-active {
        border-color: #2c5530;
        background: #f0f7f1;
    }
    .upload-foto__btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .55rem 1.15rem;
        border: none;
        border-radius: 8px;
        background: linear-gradient(135deg, #2c5530, #4a7c59);
        color: #fff;
        font-weight: 600;
        font-size: .88rem;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(44, 85, 48, .25);
        transition: filter .15s, transform .15s;
    }
    .upload-foto__btn:hover {
        color: #fff;
        filter: brightness(1.06);
        transform: translateY(-1px);
    }
    .upload-foto__nombre {
        font-size: .88rem;
        color: #64748b;
        word-break: break-all;
        flex: 1;
        min-width: 140px;
    }
    .upload-foto__nombre.has-file {
        color: #1e4620;
        font-weight: 600;
    }
    .upload-foto__img {
        max-height: 220px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .08);
    }
</style>
@endpush
@push('scripts')
<script>
(function () {
    if (window.UploadEvidenciaFoto) return;

    function initInput(input) {
        if (!input || input.dataset.uploadBound) return;
        input.dataset.uploadBound = '1';
        var id = input.id;
        var label = document.querySelector('[data-upload-label="' + id + '"]');
        var preview = document.querySelector('[data-upload-preview="' + id + '"]');
        var img = document.querySelector('[data-upload-img="' + id + '"]');
        var panel = input.closest('.upload-foto')?.querySelector('.upload-foto__panel');

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) {
                if (label) {
                    label.textContent = 'Ningún archivo seleccionado';
                    label.classList.remove('has-file');
                }
                if (preview) preview.style.display = 'none';
                if (img) img.removeAttribute('src');
                if (panel) panel.classList.remove('is-active');
                return;
            }
            if (label) {
                label.textContent = file.name;
                label.classList.add('has-file');
            }
            if (panel) panel.classList.add('is-active');
            if (!file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                if (img) img.src = e.target.result;
                if (preview) preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-upload-trigger]');
        if (!btn) return;
        var id = btn.getAttribute('data-upload-trigger');
        var input = document.getElementById(id);
        if (input) input.click();
    });

    document.querySelectorAll('.upload-foto__input').forEach(initInput);

    window.UploadEvidenciaFoto = { initInput: initInput };
})();
</script>
@endpush
@endonce
