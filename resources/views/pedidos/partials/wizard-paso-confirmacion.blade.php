{{-- PASO 4: CONFIRMACIÓN (compartido agrícola→planta y planta→mayorista) --}}

<div class="wizard-step" data-wizard-step="4">

<div class="env-wizard-panel env-confirm-panel">

    <div class="env-wizard-panel__head env-confirm-panel__head">

        <div>

            <div class="env-confirm-panel__title"><i class="fas fa-clipboard-check"></i> Resumen del envío</div>

            <p class="env-confirm-panel__subtitle mb-0">Revise que todo esté correcto antes de confirmar.</p>

        </div>

        <div class="env-confirm-badge env-confirm-badge--trayecto">

            <i class="fas fa-route mr-1"></i> <span id="conf-trayecto-tipo">Agrícola → Planta</span>

        </div>

    </div>

    <div class="env-wizard-panel__body env-confirm-panel__body">

        <div class="env-confirm-ruta-flow mb-3">

            <div class="env-confirm-ruta-punto env-confirm-ruta-punto--origen">

                <span class="env-confirm-ruta-punto__lbl"><i class="fas fa-map-marker-alt mr-1"></i> Desde</span>

                <span class="env-confirm-ruta-punto__val" id="conf-origen">—</span>

            </div>

            <div class="env-confirm-ruta-flecha" aria-hidden="true">

                <i class="fas fa-long-arrow-alt-right"></i>

            </div>

            <div class="env-confirm-ruta-punto env-confirm-ruta-punto--destino">

                <span class="env-confirm-ruta-punto__lbl"><i class="fas fa-map-marker-alt mr-1"></i> Hasta</span>

                <span class="env-confirm-ruta-punto__val" id="conf-destino">—</span>

            </div>

        </div>



        <div class="env-confirm-stats mb-3">

            <div class="env-confirm-stat env-confirm-stat--carga">

                <span class="env-confirm-stat__icon"><i class="fas fa-weight-hanging"></i></span>

                <div>

                    <span class="env-confirm-stat__lbl">Carga total</span>

                    <strong class="env-confirm-stat__val" id="conf-carga-kg">—</strong>

                </div>

            </div>

            <div class="env-confirm-stat env-confirm-stat--ruta">

                <span class="env-confirm-stat__icon"><i class="fas fa-road"></i></span>

                <div>

                    <span class="env-confirm-stat__lbl">Recorrido</span>

                    <span class="env-confirm-stat__val env-confirm-stat__val--sm" id="conf-ruta">—</span>

                </div>

            </div>

        </div>



        <div class="env-confirm-horarios mb-3" id="wizard-confirm-meta-horarios">

            <div class="env-confirm-horarios__item env-confirm-horarios__item--fecha">

                <span class="env-confirm-meta__lbl"><i class="far fa-calendar-alt mr-1"></i> Fecha entrega</span>

                <div class="env-confirm-meta__val" id="conf-fecha">—</div>

            </div>

            <div class="env-confirm-horarios__item env-confirm-horarios__item--recogida">

                <span class="env-confirm-meta__lbl"><i class="far fa-clock mr-1"></i> Hora recogida</span>

                <div class="env-confirm-meta__val" id="conf-hora-rec">—</div>

            </div>

            <div class="env-confirm-horarios__item env-confirm-horarios__item--llegada">

                <span class="env-confirm-meta__lbl"><i class="fas fa-flag-checkered mr-1"></i> Llegada estimada</span>

                <div class="env-confirm-meta__val" id="conf-hora-ent">—</div>

            </div>

        </div>



        <div class="env-confirm-asignacion mb-3">

            <div class="env-confirm-tile env-confirm-tile--chofer">

                <span class="env-confirm-tile__label"><i class="fas fa-user-tie mr-1"></i> Chofer</span>

                <div class="env-confirm-tile__value" id="conf-chofer">—</div>

            </div>

            <div class="env-confirm-tile env-confirm-tile--vehiculo">

                <span class="env-confirm-tile__label"><i class="fas fa-truck mr-1"></i> Vehículo</span>

                <div class="env-confirm-tile__value" id="conf-vehiculo">—</div>

            </div>

            <div class="env-confirm-tile env-confirm-tile--costo">

                <span class="env-confirm-tile__label"><i class="fas fa-coins mr-1"></i> Costo servicio</span>

                <div class="env-confirm-tile__value" id="conf-costo">—</div>

            </div>

        </div>



        <div class="env-confirm-productos-card env-confirm-productos-card--carga mb-3">

            <div class="env-confirm-productos-card__head" id="conf-carga-lista-titulo"><i class="fas fa-box-open mr-1"></i> Qué se recoge</div>

            <ul class="env-confirm-productos-list list-unstyled mb-0" id="conf-carga-lista"></ul>

        </div>



        <div class="form-group mb-0" id="wizard-confirm-observaciones-wrap">

            <label class="small font-weight-bold">Observaciones generales</label>

            <textarea name="observaciones" id="wizard-paso-confirmacion-observaciones" class="form-control form-control-sm" rows="2" placeholder="Instrucciones adicionales para el chofer…">{{ old('observaciones') }}</textarea>

        </div>

    </div>

</div>

</div>{{-- wizard step 4 --}}

