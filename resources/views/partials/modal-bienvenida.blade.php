@if(auth()->check() && ! auth()->user()->bienvenida_vista)
<div class="modal fade" id="modalBienvenidaAgroFusion" tabindex="-1" role="dialog" aria-labelledby="modalBienvenidaLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #2c5530, #4a7c59);">
                <h5 class="modal-title font-weight-bold" id="modalBienvenidaLabel">
                    <i class="fas fa-seedling mr-2"></i>¡Bienvenido a AgroFusion!
                </h5>
            </div>
            <div class="modal-body py-4">
                <p class="mb-3">
                    Hola <strong>{{ auth()->user()->nombre }}</strong>, tu cuenta fue aprobada y ya puedes usar el sistema.
                </p>
                <p class="mb-2">
                    Tu nombre de usuario asignado es:
                    <strong class="text-success">{{ '@'.auth()->user()->nombreusuario }}</strong>
                </p>
                <div class="alert alert-light border mb-0">
                    <i class="fas fa-info-circle text-success mr-1"></i>
                    Si deseas personalizarlo, puedes cambiar tu <strong>nombre de usuario solo una vez</strong>
                    desde <a href="{{ route('profile.show') }}">Mi perfil</a>.
                    Tu nombre, apellido, correo y teléfono no se pueden modificar desde la cuenta.
                </div>
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('profile.bienvenida.vista') }}" method="POST" class="w-100 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                    @csrf
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-success">
                        <i class="fas fa-user-edit mr-1"></i> Ir a mi perfil
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-check mr-1"></i> Entendido
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
$(function () {
    $('#modalBienvenidaAgroFusion').modal('show');
});
</script>
@endpush
@endif
