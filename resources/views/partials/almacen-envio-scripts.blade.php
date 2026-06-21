@php
    $sectionId = $sectionId ?? 'almacenSection';
    $hiddenInputId = $hiddenInputId ?? 'almacenid';
    $formSelector = $formSelector ?? null;
    $cantidadFija = $cantidadFija ?? null;
    $cantidadFijaKg = $cantidadFijaKg ?? ($cantidadFija ?? null);
    $cantidadInputId = $cantidadInputId ?? 'cantidad';
    $unidadSelectId = $unidadSelectId ?? 'unidadmedidaid';
    $productoHint = $productoHint ?? '';
    $almacenesCatalogo = $almacenesCatalogo ?? [];
    $requiereAlmacen = $requiereAlmacen ?? true;
    $resumenDestinoId = $resumenDestinoId ?? null;
    $modalId = 'modalAlmacenes-' . $sectionId;
    $mapaId = 'mapaAlmacenes-' . $sectionId;
@endphp
@if(!empty($almacenesCatalogo))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif
<script>
(function ($) {
    const sectionId = @json($sectionId);
    const hiddenInputId = @json($hiddenInputId);
    const formSelector = @json($formSelector);
    const cantidadFija = @json($cantidadFija);
    const cantidadFijaKg = @json($cantidadFijaKg);
    const cantidadInputId = @json($cantidadInputId);
    const unidadSelectId = @json($unidadSelectId);
    const productoHint = @json($productoHint);
    const almacenesCatalogo = @json($almacenesCatalogo);
    const requiereAlmacen = @json($requiereAlmacen);
    const resumenDestinoId = @json($resumenDestinoId);
    const modalId = @json($modalId);
    const mapaId = @json($mapaId);
    const almacenOptionsId = '#almacenOptions-' + sectionId;
    const almacenSeleccionadoId = '#almacen-seleccionado-' + sectionId;
    const seleccionExternaId = '#almacen-seleccion-externa-' + sectionId;
    const seleccionExternaNombreId = '#almacen-seleccion-externa-nombre-' + sectionId;

    const factores = {
        'kg': 1, 'kilogramo': 1, 'kilogramos': 1,
        'g': 0.001, 'gr': 0.001, 'gramo': 0.001,
        't': 1000, 'ton': 1000, 'tonelada': 1000, 'toneladas': 1000,
        'qq': 46, 'quintal': 46, 'quintales': 46,
        'lb': 0.453592, 'libra': 0.453592,
    };

    const mapaState = {
        map: null,
        capa: null,
        marcadores: [],
        inicializado: false,
    };

    function convertirAKg(cantidad, unidad) {
        if (!unidad) return cantidad;
        const u = String(unidad).toLowerCase();
        for (const clave in factores) {
            if (u.includes(clave)) return cantidad * factores[clave];
        }
        return cantidad;
    }

    function cardsEnSeccion() {
        return $('#' + sectionId + ' .almacen-card');
    }

    function almacenPorId(id) {
        return (almacenesCatalogo || []).find(function (a) {
            return String(a.id) === String(id);
        }) || null;
    }

    function cardPorId(id) {
        return cardsEnSeccion().filter(function () {
            return String($(this).data('id')) === String(id);
        });
    }

    function iconoTipo(tipo) {
        const t = String(tipo || '').toLowerCase();
        if (t.includes('silo')) return 'fa-database';
        if (t.includes('bodega')) return 'fa-warehouse';
        if (t.includes('fría') || t.includes('frio')) return 'fa-snowflake';
        return 'fa-box';
    }

    function actualizarSeleccionExterna(id, nombre) {
        const card = cardPorId(id);
        if (card.length) {
            $(seleccionExternaId).addClass('d-none');
            return;
        }
        if (id) {
            $(seleccionExternaNombreId).text(nombre || '');
            $(seleccionExternaId).removeClass('d-none');
        } else {
            $(seleccionExternaId).addClass('d-none');
        }
    }

    function iconoTipoResumen(tipo) {
        const t = String(tipo || '').toLowerCase();
        if (t.includes('silo')) return 'fa-database';
        if (t.includes('bodega')) return 'fa-warehouse';
        if (t.includes('fría') || t.includes('frio')) return 'fa-snowflake';
        return 'fa-box';
    }

    function barraClaseResumen(porcentaje) {
        if (porcentaje < 50) return 'low';
        if (porcentaje < 80) return 'medium';
        return 'high';
    }

    function formatearPorcentaje(valor) {
        return valor.toFixed(1).replace('.', ',');
    }

    function ingresoKgActual() {
        if (cantidadFijaKg !== null && cantidadFijaKg !== '') {
            return parseFloat(cantidadFijaKg) || 0;
        }
        return cantidadActualKg();
    }

    function proyeccionAlmacen(capacidad, disponible, ingresoKg) {
        const cap = parseFloat(capacidad) || 0;
        const disp = parseFloat(disponible) || 0;
        const ingreso = parseFloat(ingresoKg) || 0;
        const ocupado = Math.max(0, cap - disp);
        const pctActual = cap > 0 ? Math.min(100, (ocupado / cap) * 100) : 0;
        const nuevoOcupado = ocupado + ingreso;
        const pctProyectado = cap > 0 ? Math.min(100, (nuevoOcupado / cap) * 100) : 0;
        const excede = ingreso > 0 && ingreso > Math.max(0, cap - ocupado);
        const libreDespues = cap - nuevoOcupado;

        return {
            ocupado: ocupado,
            pctActual: pctActual,
            pctProyectado: pctProyectado,
            pctProyWidth: Math.max(0, pctProyectado - pctActual),
            excede: excede,
            libreDespues: libreDespues,
            ingresoKg: ingreso,
            capacidad: cap,
            disponible: disp,
        };
    }

    function htmlPreviewCapacidad(proy, compact) {
        if (!proy.ingresoKg || proy.ingresoKg <= 0) {
            return '<p class="almacen-preview-inline mb-0 text-muted">'
                + Math.round(proy.disponible).toLocaleString('es-BO') + ' kg libres de '
                + Math.round(proy.capacidad).toLocaleString('es-BO') + ' kg</p>';
        }

        const ingresoTxt = Math.round(proy.ingresoKg).toLocaleString('es-BO');
        const libreTxt = Math.round(Math.max(0, proy.libreDespues)).toLocaleString('es-BO');
        let html = '<div class="almacen-preview-inline' + (compact ? ' almacen-preview-inline--compact' : '') + '">';
        html += '<div class="almacen-preview-inline__bar capacidad-bar capacidad-bar--stacked">';
        html += '<div class="fill fill-actual ' + barraClaseResumen(proy.pctActual) + '" style="width:' + proy.pctActual + '%"></div>';
        html += '<div class="fill fill-proyeccion' + (proy.excede ? ' excede' : '') + '" style="width:' + proy.pctProyWidth + '%"></div>';
        html += '</div>';
        html += '<div class="almacen-preview-inline__text">';
        html += '<strong>+' + ingresoTxt + ' kg</strong> con esta cosecha · ';
        html += 'Ocupación: ' + formatearPorcentaje(proy.pctActual) + '% → <strong>' + formatearPorcentaje(proy.pctProyectado) + '%</strong>';
        if (proy.excede) {
            html += ' · <span class="text-danger font-weight-bold">Sin espacio</span>';
        } else {
            html += ' · Libres: <strong>' + libreTxt + ' kg</strong>';
        }
        html += '</div></div>';

        return html;
    }

    function htmlTooltipMapaAlmacen(item) {
        const proy = proyeccionAlmacen(item.capacidad, item.disponible, ingresoKgActual());
        const ubicacion = item.direccion || item.ubicacion || '';

        let html = '<div class="almacen-mapa-tooltip-rich__inner">';
        html += '<strong class="almacen-mapa-tooltip-rich__nombre">' + (item.nombre || '') + '</strong>';
        html += '<div class="almacen-mapa-tooltip-rich__meta">' + (item.tipo || 'General');
        if (ubicacion) html += ' · ' + ubicacion;
        html += '</div>';
        html += '<div class="almacen-mapa-tooltip-rich__cap">Capacidad: <strong>'
            + Math.round(proy.capacidad).toLocaleString('es-BO') + ' kg</strong></div>';
        if (proy.ingresoKg > 0) {
            html += htmlPreviewCapacidad(proy, true);
        } else {
            html += '<div class="almacen-mapa-tooltip-rich__libre">'
                + Math.round(proy.disponible).toLocaleString('es-BO') + ' kg disponibles</div>';
        }
        html += '</div>';

        return html;
    }

    function actualizarResumenDestino(id) {
        if (!resumenDestinoId) return;
        const el = document.getElementById(resumenDestinoId);
        if (!el) return;

        const item = almacenPorId(id);
        if (!item) {
            el.innerHTML = '<div class="envio-destino-card envio-destino-card--empty">'
                + '<i class="fas fa-warehouse fa-2x text-muted mb-2"></i>'
                + '<p class="mb-0 small text-muted">Use <strong>Cambiar almacén</strong> para elegir el destino.</p></div>';
            return;
        }

        const proy = proyeccionAlmacen(item.capacidad, item.disponible, ingresoKgActual());
        const ubicacion = item.direccion || item.ubicacion || '';
        const fillClass = barraClaseResumen(proy.pctActual);
        const ingresoTxt = proy.ingresoKg > 0 ? Math.round(proy.ingresoKg).toLocaleString('es-BO') : '';

        let html = '<div class="envio-destino-card">';
        html += '<div class="envio-destino-card__head"><div>';
        html += '<span class="envio-destino-card__badge"><i class="fas fa-map-marker-alt mr-1"></i> Destino de envío</span>';
        html += '<h6 class="envio-destino-card__nombre mb-1"><i class="fas fa-warehouse mr-1"></i>' + (item.nombre || '') + '</h6>';
        html += '<p class="envio-destino-card__meta mb-0">' + (item.tipo || 'General');
        if (ubicacion) html += ' · ' + ubicacion;
        html += '</p></div>';
        if (ingresoTxt) {
            html += '<div class="envio-destino-card__ingreso text-right">';
            html += '<span class="envio-destino-card__ingreso-label">A ingresar</span>';
            html += '<strong class="envio-destino-card__ingreso-valor">+' + ingresoTxt + ' kg</strong></div>';
        }
        html += '</div>';
        html += '<div class="envio-destino-card__capacidad">';
        html += '<div class="d-flex justify-content-between small mb-1">';
        html += '<span class="text-muted">Capacidad total: <strong>' + Math.round(proy.capacidad).toLocaleString('es-BO') + ' kg</strong></span>';
        html += '<span class="text-muted">Ocupado hoy: <strong>' + formatearPorcentaje(proy.pctActual) + '%</strong></span>';
        html += '</div>';
        html += '<div class="capacidad-bar capacidad-bar--stacked">';
        html += '<div class="fill fill-actual ' + fillClass + '" style="width:' + proy.pctActual + '%"></div>';
        if (proy.ingresoKg > 0) {
            html += '<div class="fill fill-proyeccion' + (proy.excede ? ' excede' : '') + '" style="width:' + proy.pctProyWidth + '%"></div>';
        }
        html += '</div>';
        if (proy.ingresoKg > 0) {
            html += '<div class="envio-destino-card__preview mt-2">';
            html += '<div class="envio-destino-card__preview-principal">Tras el envío: ocupación <strong>'
                + formatearPorcentaje(proy.pctProyectado) + '%</strong></div>';
            html += '<div class="envio-destino-card__preview-detalle">';
            if (proy.excede) {
                html += '<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Sin espacio suficiente</span>';
            } else {
                html += 'Quedan <strong>' + Math.round(Math.max(0, proy.libreDespues)).toLocaleString('es-BO') + ' kg</strong> libres';
            }
            html += '</div></div>';
        } else {
            html += '<p class="small text-muted mb-0 mt-2">'
                + Math.round(proy.disponible).toLocaleString('es-BO') + ' kg disponibles de '
                + Math.round(proy.capacidad).toLocaleString('es-BO') + ' kg</p>';
        }
        html += '</div></div>';

        el.innerHTML = html;
    }

    function aplicarSeleccion(id, nombre, disponible, card) {
        cardsEnSeccion().removeClass('selected').css({ background: 'white', borderColor: '#dee2e6' });
        cardsEnSeccion().find('.fa-check-circle').hide();

        if (card && card.length) {
            card.addClass('selected');
            card.find('.fa-check-circle').show();
        }

        $('#' + hiddenInputId).val(id);
        $(almacenSeleccionadoId).html('<i class="fas fa-warehouse mr-1"></i> <strong>Almacén:</strong> ' + (nombre || ''));
        actualizarSeleccionExterna(id, nombre);

        const cantidad = cantidadActual();
        const cardRef = card && card.length ? card : null;
        if (cantidad > 0 && cardRef) {
            verificarCapacidad(cantidad, disponible, cardRef);
        } else if (cantidad > 0 && disponible !== undefined) {
            verificarCapacidadExterna(cantidad, disponible, id);
        } else {
            $('#alertaCapacidad-' + sectionId).remove();
        }

        actualizarResumenDestino(id);
        actualizarPrevisualizacionCosecha();
    }

    function seleccionarAlmacenPorId(id, cerrarModal) {
        const card = cardPorId(id);
        const item = almacenPorId(id);
        let nombre;
        let disponible;

        if (item) {
            nombre = item.nombre;
            disponible = item.disponible;
        } else if (card.length) {
            nombre = card.data('nombre');
            disponible = card.data('disponible');
        } else {
            return;
        }

        aplicarSeleccion(id, nombre, disponible, card.length ? card : null);

        if (cerrarModal && $('#' + modalId).length) {
            $('#' + modalId).modal('hide');
        }

        resaltarModalLista(id);
        resaltarMarcadoresMapa(id);
    }

    function recomendarAlmacen(texto) {
        if (!texto) return;
        texto = texto.toLowerCase();
        const mapeo = {
            'maiz': ['silo', 'grano'], 'maíz': ['silo', 'grano'],
            'soya': ['silo', 'grano'], 'trigo': ['silo', 'grano'], 'arroz': ['silo', 'grano'],
            'papa': ['bodega', 'frio', 'tuberculo', 'refrigerado'],
            'frita': ['bodega', 'empaque'], 'snack': ['bodega', 'empaque'],
            'caña': ['bodega', 'zafra'],
            'fruta': ['frio', 'refrigerado'], 'cítrico': ['bodega'],
        };
        let keywords = [];
        let foundKey = null;
        for (const key in mapeo) {
            if (texto.includes(key)) {
                keywords = mapeo[key];
                foundKey = key;
                break;
            }
        }
        if (keywords.length === 0) keywords = [texto];

        let mejor = null;
        let maxScore = 0;

        const candidatos = (almacenesCatalogo.length ? almacenesCatalogo : cardsEnSeccion().map(function () {
            return {
                id: $(this).data('id'),
                nombre: $(this).data('nombre'),
                tags: String($(this).data('tags') || ''),
                disponible: $(this).data('disponible'),
            };
        }).get());

        candidatos.forEach(function (item) {
            const tags = String(item.tags || '');
            let score = 0;
            keywords.forEach(function (word) {
                if (tags.includes(word)) score += 2;
            });
            if (foundKey && tags.includes(foundKey)) score += 10;
            if (tags.includes(texto)) score += 5;

            if (score > maxScore) {
                maxScore = score;
                mejor = item;
            }
        });

        cardsEnSeccion().each(function () {
            const tags = String($(this).data('tags') || '');
            let score = 0;
            keywords.forEach(function (word) {
                if (tags.includes(word)) score += 2;
            });
            if (foundKey && tags.includes(foundKey)) score += 10;
            if (tags.includes(texto)) score += 5;

            if (score > 0) {
                if (score >= 10) {
                    $(this).css('border-color', '#2c5530').css('background', '#d4edda');
                } else {
                    $(this).css('border-color', '#17a2b8').css('background', '#f0fcff');
                }
            } else {
                $(this).css('border-color', '#dee2e6').css('background', 'white');
            }
        });

        if (mejor && requiereAlmacen && !$('#' + hiddenInputId).val()) {
            seleccionarAlmacenPorId(mejor.id, false);
            $(almacenOptionsId + ' .alert-suggestion').remove();
            $(almacenOptionsId).prepend(
                '<div class="alert alert-info alert-dismissible fade show p-2 small mb-2 alert-suggestion" role="alert">' +
                '<i class="fas fa-lightbulb mr-1"></i> Sugerencia: <strong>' + mejor.nombre + '</strong>' +
                ' (adecuado para ' + texto + ')' +
                '<button type="button" class="close p-2" data-dismiss="alert"><span>&times;</span></button></div>'
            );
        }
    }

    function cantidadActual() {
        if (cantidadFija !== null && cantidadFija !== '') {
            return parseFloat(cantidadFija) || 0;
        }
        return parseFloat($('#' + cantidadInputId).val()) || 0;
    }

    function cantidadActualKg() {
        if (cantidadFijaKg !== null && cantidadFijaKg !== '') {
            return parseFloat(cantidadFijaKg) || 0;
        }
        if (cantidadFija !== null && cantidadFija !== '') {
            return parseFloat(cantidadFija) || 0;
        }
        return convertirAKg(cantidadActual(), unidadActual());
    }

    function unidadActual() {
        if (cantidadFija !== null && cantidadFija !== '') {
            return 'kg';
        }
        const opt = $('#' + unidadSelectId + ' option:selected');
        return opt.data('abrev') || opt.text() || 'kg';
    }

    function verificarCapacidad(cantidad, disponible, card) {
        const umProduccion = unidadActual();
        const umAlmacen = card.data('um-almacen');
        const cantidadKg = convertirAKg(cantidad, umProduccion);
        const disponibleKg = convertirAKg(disponible, umAlmacen);

        if (cantidad > 0 && cantidadKg > disponibleKg) {
            card.css('border-color', '#dc3545');
            if ($('#alertaCapacidad-' + sectionId).length === 0) {
                $(almacenOptionsId).prepend(
                    '<div id="alertaCapacidad-' + sectionId + '" class="alert alert-danger p-2 small mb-2">' +
                    '⚠️ Excede capacidad: ' + cantidad + ' ' + umProduccion + ' &gt; disp. ' +
                    disponible + ' ' + umAlmacen + '</div>'
                );
            }
        } else {
            $('#alertaCapacidad-' + sectionId).remove();
            if (card.hasClass('selected')) {
                card.css('border-color', '#28a745');
            }
        }
    }

    function verificarCapacidadExterna(cantidad, disponible, id) {
        const item = almacenPorId(id);
        if (!item) return;

        const umProduccion = unidadActual();
        const umAlmacen = item.um || 'kg';
        const cantidadKg = convertirAKg(cantidad, umProduccion);
        const disponibleKg = convertirAKg(disponible, umAlmacen);

        if (cantidad > 0 && cantidadKg > disponibleKg) {
            if ($('#alertaCapacidad-' + sectionId).length === 0) {
                $(almacenOptionsId).prepend(
                    '<div id="alertaCapacidad-' + sectionId + '" class="alert alert-danger p-2 small mb-2">' +
                    '⚠️ Excede capacidad: ' + cantidad + ' ' + umProduccion + ' &gt; disp. ' +
                    disponible + ' ' + umAlmacen + '</div>'
                );
            }
        } else {
            $('#alertaCapacidad-' + sectionId).remove();
        }
    }

    function resetBarraAlmacenCard(card) {
        const capacidad = parseFloat(card.data('capacidad')) || 0;
        const ocupado = parseFloat(card.data('ocupado')) || 0;
        const pctActual = capacidad > 0 ? Math.min(100, (ocupado / capacidad) * 100) : 0;

        card.find('.capacidad-bar .fill-actual').css('width', pctActual + '%');
        card.find('.capacidad-bar .fill-proyeccion').css('width', '0%').removeClass('excede');
        card.find('.almacen-preview-cosecha').hide().empty();
    }

    function formatearPorcentaje(valor) {
        return valor.toFixed(1).replace('.', ',');
    }

    function htmlPrevisualizacionCosecha(ingresoKg, pctActual, pctProyectado, excede, libreDespues) {
        const ingresoTxt = Math.round(ingresoKg).toLocaleString('es-BO');
        const libreTxt = Math.round(libreDespues).toLocaleString('es-BO');
        const pctAntes = formatearPorcentaje(pctActual);
        const pctDespues = formatearPorcentaje(pctProyectado);

        let html = '<div class="almacen-preview-cosecha__principal">';
        html += '<strong>+' + ingresoTxt + ' kg</strong> entrarían con esta cosecha';
        html += '</div>';
        html += '<div class="almacen-preview-cosecha__detalle">';
        html += 'Ocupación: ' + pctAntes + '% → <strong>' + pctDespues + '%</strong>';

        if (excede) {
            html += ' · <span class="text-danger font-weight-bold">Sin espacio suficiente</span>';
        } else {
            html += ' · Quedan <strong>' + libreTxt + ' kg</strong> libres';
        }

        html += '</div>';

        return html;
    }

    function actualizarPrevisualizacionCosecha() {
        const ingresoKg = cantidadActualKg();
        const selectedId = $('#' + hiddenInputId).val();

        cardsEnSeccion().each(function () {
            const card = $(this);
            const esSeleccionado = card.hasClass('selected')
                || (selectedId && String(card.data('id')) === String(selectedId));

            if (!esSeleccionado) {
                resetBarraAlmacenCard(card);
                return;
            }

            const capacidad = parseFloat(card.data('capacidad')) || 0;
            const ocupado = parseFloat(card.data('ocupado')) || 0;
            const previewEl = card.find('.almacen-preview-cosecha');
            const barActual = card.find('.capacidad-bar .fill-actual');
            const barProyeccion = card.find('.capacidad-bar .fill-proyeccion');

            if (ingresoKg <= 0 || capacidad <= 0) {
                resetBarraAlmacenCard(card);
                return;
            }

            const pctActual = Math.min(100, (ocupado / capacidad) * 100);
            const nuevoOcupado = ocupado + ingresoKg;
            const pctProyectado = Math.min(100, (nuevoOcupado / capacidad) * 100);
            const excede = ingresoKg > Math.max(0, capacidad - ocupado);
            const libreDespues = capacidad - nuevoOcupado;

            barActual.css('width', pctActual + '%');
            barProyeccion
                .css('width', Math.max(0, pctProyectado - pctActual) + '%')
                .toggleClass('excede', excede);

            previewEl
                .html(htmlPrevisualizacionCosecha(ingresoKg, pctActual, pctProyectado, excede, libreDespues))
                .show();
        });
    }

    function revisarCapacidadSeleccionada() {
        const cantidad = cantidadActual();
        const id = $('#' + hiddenInputId).val();
        const card = cardsEnSeccion().filter('.selected');
        if (card.length) {
            verificarCapacidad(cantidad, card.data('disponible'), card);
        } else if (id) {
            const item = almacenPorId(id);
            if (item) verificarCapacidadExterna(cantidad, item.disponible, id);
        }
    }

    function renderModalLista(items) {
        const cont = $('#' + modalId + '-lista-items');
        const sinRes = $('#' + modalId + '-sin-resultados');
        const resumen = $('#' + modalId + '-resultados');
        const seleccionado = $('#' + hiddenInputId).val();

        cont.empty();
        if (!items.length) {
            sinRes.removeClass('d-none');
            resumen.text('0 almacenes encontrados');
            return;
        }

        sinRes.addClass('d-none');
        resumen.text(items.length + ' almacén' + (items.length === 1 ? '' : 'es') + ' encontrado' + (items.length === 1 ? '' : 's'));

        items.forEach(function (item) {
            const sel = String(seleccionado) === String(item.id) ? ' is-selected' : '';
            const proy = proyeccionAlmacen(item.capacidad, item.disponible, ingresoKgActual());
            const previewHtml = htmlPreviewCapacidad(proy, false);
            const html =
                '<div class="almacen-modal-item' + sel + '" data-id="' + item.id + '">' +
                    '<div class="almacen-modal-icon"><i class="fas ' + iconoTipo(item.tipo) + '"></i></div>' +
                    '<div class="almacen-modal-body">' +
                        '<div class="almacen-modal-nombre">' + item.nombre + '</div>' +
                        '<div class="almacen-modal-meta">' +
                            (item.tipo || 'General') +
                            (item.ubicacion ? ' • ' + item.ubicacion : '') +
                        '</div>' +
                        '<div class="small mt-1">' +
                            '<span class="text-muted">Capacidad: </span>' +
                            '<strong>' + Number(item.capacidad).toLocaleString('es-BO') + ' kg</strong>' +
                            '<span class="text-muted"> · Libre hoy: </span>' +
                            '<span class="text-success font-weight-bold">' + Number(item.disponible).toLocaleString('es-BO') + ' kg</span>' +
                        '</div>' +
                        '<div class="almacen-modal-preview' + (sel ? '' : ' d-none') + '">' + previewHtml + '</div>' +
                    '</div>' +
                    '<div class="almacen-modal-item__chevron"><i class="fas fa-chevron-right text-muted"></i></div>' +
                '</div>';
            cont.append(html);
        });
    }

    function filtrarCatalogo() {
        const nombre = ($('#' + modalId + '-filtro-nombre').val() || '').toLowerCase().trim();
        const capMin = parseFloat($('#' + modalId + '-filtro-cap-min').val());
        const capMax = parseFloat($('#' + modalId + '-filtro-cap-max').val());

        return (almacenesCatalogo || []).filter(function (item) {
            const texto = (item.nombre + ' ' + (item.tipo || '') + ' ' + (item.ubicacion || '') + ' ' + (item.tags || '')).toLowerCase();
            if (nombre && !texto.includes(nombre)) return false;

            const dispKg = convertirAKg(parseFloat(item.disponible) || 0, item.um || 'kg');
            if (!isNaN(capMin) && dispKg < capMin) return false;
            if (!isNaN(capMax) && dispKg > capMax) return false;

            return true;
        });
    }

    function aplicarFiltrosModal() {
        renderModalLista(filtrarCatalogo());
    }

    function resaltarModalLista(id) {
        $('#' + modalId + '-lista-items .almacen-modal-item').removeClass('is-selected');
        $('#' + modalId + '-lista-items .almacen-modal-preview').addClass('d-none');
        const sel = $('#' + modalId + '-lista-items .almacen-modal-item').filter(function () {
            return String($(this).data('id')) === String(id);
        });
        sel.addClass('is-selected');
        sel.find('.almacen-modal-preview').removeClass('d-none');
    }

    function iconAlmacenMapa(seleccionado) {
        const sel = seleccionado ? ' is-selected' : '';
        return L.divIcon({
            html: '<div class="almacen-mapa-pin' + sel + '"><i class="fas fa-warehouse"></i></div>',
            className: 'almacen-mapa-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function flashMapa(texto) {
        const el = document.getElementById(mapaId + '-flash');
        if (!el) return;
        el.textContent = texto;
        el.style.display = 'block';
        clearTimeout(flashMapa._t);
        flashMapa._t = setTimeout(function () { el.style.display = 'none'; }, 2200);
    }

    function resaltarMarcadoresMapa(idSeleccionado) {
        if (!mapaState.capa) return;
        mapaState.capa.eachLayer(function (layer) {
            if (!layer._almacenId) return;
            const sel = String(layer._almacenId) === String(idSeleccionado);
            layer.setIcon(iconAlmacenMapa(sel));
            layer.setZIndexOffset(sel ? 200 : 0);
        });
    }

    function initMapaModal() {
        if (mapaState.inicializado || !document.getElementById(mapaId) || typeof L === 'undefined') {
            if (mapaState.map) mapaState.map.invalidateSize();
            return;
        }

        const items = (almacenesCatalogo || []).filter(function (a) {
            return a.lat != null && a.lng != null;
        });

        mapaState.map = L.map(mapaId, { scrollWheelZoom: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(mapaState.map);

        mapaState.capa = L.layerGroup().addTo(mapaState.map);
        const bounds = [];
        const seleccionado = $('#' + hiddenInputId).val();

        items.forEach(function (item) {
            const lat = parseFloat(item.lat);
            const lng = parseFloat(item.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const sel = String(seleccionado) === String(item.id);
            const marker = L.marker([lat, lng], {
                icon: iconAlmacenMapa(sel),
                zIndexOffset: sel ? 200 : 0,
            })
                .bindTooltip(htmlTooltipMapaAlmacen(item), {
                    className: 'almacen-mapa-tooltip-rich',
                    direction: 'top',
                    offset: [0, -14],
                    opacity: 1,
                    sticky: true,
                })
                .addTo(mapaState.capa);

            marker._almacenId = item.id;
            marker._almacenItem = item;
            marker.on('mouseover', function () {
                const tip = this.getTooltip();
                if (tip) tip.setContent(htmlTooltipMapaAlmacen(item));
                this.openTooltip();
            });
            marker.on('click', function () {
                seleccionarAlmacenPorId(item.id, true);
                flashMapa('Seleccionado: ' + item.nombre);
            });
            bounds.push([lat, lng]);
        });

        if (bounds.length) {
            try {
                mapaState.map.fitBounds(L.latLngBounds(bounds).pad(0.12));
            } catch (e) {
                mapaState.map.setView(bounds[0], 12);
            }
        } else {
            mapaState.map.setView([-17.7833, -63.1821], 11);
        }

        mapaState.inicializado = true;
    }

    window.AlmacenEnvio = window.AlmacenEnvio || {};
    window.AlmacenEnvio.recomendar = recomendarAlmacen;
    window.AlmacenEnvio.seleccionar = function (id) {
        seleccionarAlmacenPorId(id, false);
    };
    window.AlmacenEnvio.actualizarPreview = actualizarPrevisualizacionCosecha;

    $(function () {
        if (requiereAlmacen && cardsEnSeccion().length === 1 && !$('#' + hiddenInputId).val()) {
            cardsEnSeccion().first().trigger('click');
        }

        const seleccionInicial = $('#' + hiddenInputId).val();
        if (seleccionInicial) {
            const item = almacenPorId(seleccionInicial);
            if (item) actualizarSeleccionExterna(seleccionInicial, item.nombre);
            actualizarResumenDestino(seleccionInicial);
        }

        if (productoHint) {
            recomendarAlmacen(productoHint);
        }

        if (formSelector && requiereAlmacen) {
            $(formSelector).on('submit', function (e) {
                if (!$('#' + hiddenInputId).val()) {
                    e.preventDefault();
                    alert('Debe seleccionar un almacén antes de continuar.');
                    document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        cardsEnSeccion().on('click', function () {
            const $card = $(this);
            aplicarSeleccion(
                $card.data('id'),
                $card.data('nombre'),
                $card.data('disponible'),
                $card
            );
        });

        cardsEnSeccion().on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        if (cantidadFija === null) {
            $('#' + cantidadInputId + ', #' + unidadSelectId).on('change keyup input', function () {
                actualizarPrevisualizacionCosecha();
                revisarCapacidadSeleccionada();
            });
        } else {
            actualizarPrevisualizacionCosecha();
            revisarCapacidadSeleccionada();
        }

        actualizarPrevisualizacionCosecha();

        if (almacenesCatalogo.length && $('#' + modalId).length) {
            $('#' + modalId).on('shown.bs.modal', function () {
                aplicarFiltrosModal();
            });

            $('#' + modalId + '-filtro-nombre, #' + modalId + '-filtro-cap-min, #' + modalId + '-filtro-cap-max')
                .on('input change', aplicarFiltrosModal);

            $(document).on('click', '#' + modalId + '-lista-items .almacen-modal-item', function () {
                seleccionarAlmacenPorId($(this).data('id'), true);
            });

            $(document).on('mouseenter', '#' + modalId + '-lista-items .almacen-modal-item', function () {
                $('#' + modalId + '-lista-items .almacen-modal-preview').addClass('d-none');
                $(this).find('.almacen-modal-preview').removeClass('d-none');
            });

            $(document).on('mouseleave', '#' + modalId + '-lista-items .almacen-modal-item', function () {
                if (!$(this).hasClass('is-selected')) {
                    $(this).find('.almacen-modal-preview').addClass('d-none');
                }
            });

            $('a[href="#' + modalId + '-mapa"]').on('shown.bs.tab', function () {
                if (mapaState.map) {
                    mapaState.map.remove();
                    mapaState.map = null;
                    mapaState.capa = null;
                    mapaState.inicializado = false;
                }
                window.setTimeout(initMapaModal, 120);
            });

            $(document).on('click', '.btn-cambiar-almacen-modal[data-section="' + sectionId + '"]', function () {
                $('#' + modalId).modal('show');
            });
        }
    });
})(jQuery);
</script>
