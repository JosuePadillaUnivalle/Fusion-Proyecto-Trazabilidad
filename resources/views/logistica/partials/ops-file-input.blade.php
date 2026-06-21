@php
    $inputId = $inputId ?? ('logOpsFile_' . uniqid());
    $name = $name ?? 'archivo';
    $accept = $accept ?? '.pdf,.jpg,.jpeg,.png';
    $required = $required ?? false;
    $placeholder = $placeholder ?? 'PDF, JPG o PNG';
@endphp

<div class="log-ops-file-wrap">
    <input type="file"
           class="log-ops-file-input"
           id="{{ $inputId }}"
           name="{{ $name }}"
           accept="{{ $accept }}"
           @if($required) required @endif>
    <label for="{{ $inputId }}" class="log-ops-file-btn">
        <i class="fas fa-paperclip mr-1"></i> Elegir archivo
    </label>
    <span class="log-ops-file-name" data-log-ops-file-name>{{ $placeholder }}</span>
</div>

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.log-ops-file-wrap').forEach(function (wrap) {
            var input = wrap.querySelector('.log-ops-file-input');
            var nameEl = wrap.querySelector('[data-log-ops-file-name]');
            if (!input || !nameEl) {
                return;
            }
            var placeholder = nameEl.textContent.trim();
            input.addEventListener('change', function () {
                if (input.files && input.files[0]) {
                    nameEl.textContent = input.files[0].name;
                    wrap.classList.add('has-file');
                } else {
                    nameEl.textContent = placeholder;
                    wrap.classList.remove('has-file');
                }
            });
        });
    });
    </script>
    @endpush
@endonce
