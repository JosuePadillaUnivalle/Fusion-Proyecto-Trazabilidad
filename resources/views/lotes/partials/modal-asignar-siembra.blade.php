<div class="modal fade" id="modalAsignarSiembra" tabindex="-1" role="dialog" aria-labelledby="modalAsignarSiembraLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">

            <form method="POST" action="{{ route('lotes.siembra.asignar', $lote) }}" id="formAsignarSiembra">

                @csrf

                <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #14532d, #22c55e); color: #fff;">

                    <h5 class="modal-title font-weight-bold mb-0" id="modalAsignarSiembraLabel">

                        <i class="fas fa-seedling mr-2"></i>Asignar siembra

                    </h5>

                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">

                        <span aria-hidden="true">&times;</span>

                    </button>

                </div>

                <div class="modal-body px-4 py-4">

                    <p class="mb-3">

                        Indique quién va a sembrar el lote <strong>{{ $lote->nombre }}</strong>.

                        Cuando lo realice, el agricultor asignado podrá marcarla como completada con una foto.

                    </p>



                    @if($puede_designar_responsable_siembra ?? false)

                        <label class="small font-weight-bold text-muted d-block mb-2">Quién va a sembrar <span class="text-danger">*</span></label>

                        @include('partials.selector-catalogo', [

                            'id' => 'siembra_responsable',

                            'name' => 'usuarioid',

                            'value' => old('usuarioid', $responsable_siembra_inicial ?? ''),

                            'labelSelected' => $responsable_siembra_label ?? '',

                            'endpoint' => route('catalogo-selector.usuarios'),

                            'params' => $responsable_siembra_params ?? ['roles' => 'agricultor'],

                            'title' => 'Agricultor responsable',

                            'searchPlaceholder' => 'Nombre del agricultor…',

                            'searchLabel' => 'Buscar responsable',

                            'modalIcon' => 'fa-user',

                            'rowIcon' => 'fa-user',

                            'required' => true,

                            'inputGroup' => true,

                        ])

                        @error('usuarioid')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror

                    @else

                        <input type="hidden" name="usuarioid" value="{{ $responsable_siembra_inicial }}">

                        <div class="alert alert-light border small mb-0 py-2">

                            <i class="fas fa-user-check text-success mr-1"></i>

                            Responsable: <strong>{{ $responsable_siembra_label ?: 'Usted' }}</strong>

                        </div>

                    @endif

                    @error('siembra')<small class="text-danger d-block mt-2">{{ $message }}</small>@enderror

                </div>

                <div class="modal-footer border-0 bg-light px-4 py-3">

                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>

                    <button type="submit" class="btn btn-success font-weight-bold px-4">

                        <i class="fas fa-user-plus mr-1"></i> Asignar siembra

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

