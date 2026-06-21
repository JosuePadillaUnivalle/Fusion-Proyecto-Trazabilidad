@if (session('error'))
    <div class="ag-flash ag-flash--error" role="alert">
        <span class="ag-flash__icon"><i class="fas fa-ban"></i></span>
        <span class="ag-flash__text">{{ session('error') }}</span>
        <button type="button" class="ag-flash__close" aria-label="Cerrar"><i class="fas fa-times"></i></button>
    </div>
@endif

@if ($errors->any())
    <div class="ag-flash ag-flash--error" role="alert">
        <span class="ag-flash__icon"><i class="fas fa-ban"></i></span>
        <div class="ag-flash__text">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        <button type="button" class="ag-flash__close" aria-label="Cerrar"><i class="fas fa-times"></i></button>
    </div>
@endif

@once
@push('scripts')
<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.ag-flash__close');
    if (!btn) return;
    e.preventDefault();
    var flash = btn.closest('.ag-flash');
    if (flash) flash.remove();
});
</script>
@endpush
@endonce
