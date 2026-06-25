@php
    $sectionId = $sectionId ?? 'almacenSection';
    $hiddenInputId = $hiddenInputId ?? 'almacenid';
    $formSelector = $formSelector ?? null;
    $cantidadFija = $cantidadFija ?? null;
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
        return $('#' + sectionId + ' .almacen-destacados-grid:not(.d-none) .almacen-card, #' + sectionId + ' #almacenesContainer-' + sectionId + ' .almacen-card');
    }

    function cardPorId(id) {
        return $('#' + sectionId + ' .almacen-card').filter(function () {
            return String($(this).data('id')) === String(id);
        });
    }

    function almacenPorId(id) {
        return (almacenesCatalogo || []).find(function (a) {
            return String(a.id) === String(id);
        }) || null;
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

    function contenedorAlertas() {
        const opts = $(almacenOptionsId);
        return opts.length ? opts : $('#' + sectionId);
    }

    function itemDesdeCard($card) {
        const id = $card.data('id');
        const catalogo = almacenPorId(id);
        if (catalogo) return catalogo;
        return {
            id: id,
            nombre: $card.data('nombre') || '',
            tipo: $card.data('tipo') || 'General',
            ubicacion: $card.data('ubicacion') || '',
            disponible: parseFloat($card.data('disponible')) || 0,
            capacidad: parseFloat($card.data('capacidad')) || 0,
            um: $card.data('um-almacen') || 'kg',
        };
    }

    function metricasCapacidadItem(item) {
        const cap = parseFloat(item.capacidad) || 0;
        const disp = parseFloat(item.disponible) || 0;
        const usado = Math.max(0, cap - disp);
        const pctUsado = cap > 0 ? (usado / cap) * 100 : 0;
        const cantidadKg = calcularProyeccionKg();
        const pctCosecha = cap > 0 && cantidadKg > 0 ? (cantidadKg / cap) * 100 : 0;
        const fillClass = barraClaseResumen(pctUsado);
        return { cap, disp, usado, pctUsado, cantidadKg, pctCosecha, fillClass };
    }

    function htmlBarraApilada(pctUsado, pctCosecha, fillClass) {
        const u = Math.min(pctUsado, 100);
        const c = Math.min(pctCosecha, Math.max(0, 100 - u));
        let html = '<div class="capacidad-bar capacidad-bar--stacked">';
        html += '<div class="fill ' + fillClass + '" style="width:' + u + '%"></div>';
        if (c > 0) {
            html += '<div class="fill-proyeccion ' + fillClass + '" style="left:' + u + '%;width:' + c + '%;display:block;"></div>';
        }
        html += '</div>';
        return html;
    }

    function htmlLineaProyeccion(cantidadKg, pctCosecha, disp) {
        if (cantidadKg <= 0) return '';
        const fmt = cantidadKg.toLocaleString('es-BO', { maximumFractionDigits: 0 });
        let txt = 'Esta cosecha ocupará +' + fmt + ' kg';
        if (pctCosecha > 0) {
            txt += ' (' + pctCosecha.toFixed(1) + '% del almacén)';
        }
        if (cantidadKg > disp) {
            txt += ' — excede lo disponible';
        }
        return '<p class="almacen-proyeccion-texto mb-0">' + txt + '</p>';
    }

    function htmlPreviewAlmacen(item, modo) {
        if (!item) return '';
        const m = metricasCapacidadItem(item);
        const ubicacion = item.direccion || item.ubicacion || '';
        const esMapa = modo === 'mapa';

        if (esMapa) {
            return (
                '<div class="almacen-hover-preview__titulo">' + (item.nombre || '') + '</div>' +
                '<div class="small text-muted mb-1">' +
                    Math.round(m.disp).toLocaleString('es-BO') + ' / ' +
                    Math.round(m.cap).toLocaleString('es-BO') + ' kg disp.' +
                '</div>' +
                htmlBarraApilada(m.pctUsado, m.pctCosecha, m.fillClass) +
                (m.cantidadKg > 0 ? htmlLineaProyeccion(m.cantidadKg, m.pctCosecha, m.disp) : '')
            );
        }

        return (
            '<div class="almacen-hover-preview__titulo"><i class="fas fa-warehouse text-success mr-1"></i>' + (item.nombre || '') + '</div>' +
            '<div class="almacen-hover-preview__meta">' + (item.tipo || 'General') + (ubicacion ? ' · ' + ubicacion : '') + '</div>' +
            '<div class="small">' +
                '<span class="text-success font-weight-bold">' + Math.round(m.disp).toLocaleString('es-BO') + '</span>' +
                '<span class="text-muted"> / ' + Math.round(m.cap).toLocaleString('es-BO') + ' ' + (item.um || 'kg') + ' disponibles</span>' +
            '</div>' +
            htmlBarraApilada(m.pctUsado, m.pctCosecha, m.fillClass) +
            (m.cantidadKg > 0
                ? htmlLineaProyeccion(m.cantidadKg, m.pctCosecha, m.disp)
                : '<p class="small text-muted mb-0 mt-1">' + m.pctUsado.toFixed(1) + ' % ocupado</p>')
        );
    }

    function calcularProyeccionKg() {
        const cantidad = cantidadActual();
        if (cantidad <= 0) return 0;
        return convertirAKg(cantidad, unidadActual());
    }

    function actualizarProyeccionTarjetas() {
        const selectedId = $('#' + hiddenInputId).val();
        const cantidadKg = calcularProyeccionKg();

        $('#' + sectionId + ' .almacen-card').each(function () {
            const $card = $(this);
            const $proy = $card.find('.fill-proyeccion');
            const $txt = $card.find('.almacen-proyeccion-texto');
            const esSeleccionada = String($card.data('id')) === String(selectedId);

            if (!esSeleccionada || cantidadKg <= 0) {
                $proy.hide().css({ width: '0', left: '0' });
                $txt.addClass('d-none').text('');
                return;
            }

            const cap = parseFloat($card.data('capacidad')) || 0;
            const disp = parseFloat($card.data('disponible')) || 0;
            const usado = Math.max(0, cap - disp);
            const pctUsado = cap > 0 ? (usado / cap) * 100 : 0;
            const pctCosecha = cap > 0 ? (cantidadKg / cap) * 100 : 0;
            const $fillBase = $card.find('.capacidad-bar > .fill').first();
            let fillClass = 'low';
            if ($fillBase.hasClass('high')) fillClass = 'high';
            else if ($fillBase.hasClass('medium')) fillClass = 'medium';
            else fillClass = barraClaseResumen(pctUsado);

            $proy.removeClass('low medium high').addClass(fillClass);
            $proy.css({
                left: Math.min(pctUsado, 100) + '%',
                width: Math.min(pctCosecha, Math.max(0, 100 - pctUsado)) + '%',
            }).show();

            $txt.removeClass('d-none').text(
                (function () {
                    const fmt = cantidadKg.toLocaleString('es-BO', { maximumFractionDigits: 0 });
                    let msg = 'Esta cosecha ocupará +' + fmt + ' kg';
                    if (pctCosecha > 0) msg += ' (' + pctCosecha.toFixed(1) + '% del almacén)';
                    if (cantidadKg > disp) msg += ' — excede lo disponible';
                    return msg;
                })()
            );
        });
    }

    function actualizarPreviewPanelModal(item) {
        const panel = document.getElementById(modalId + '-preview-panel');
        if (!panel) return;
        if (!item) {
            panel.innerHTML =
                '<p class="text-muted small mb-0">' +
                    '<i class="fas fa-mouse-pointer mr-1"></i>' +
                    'Pase el cursor sobre un almacén o haga clic para ver la previsualización.' +
                '</p>';
            return;
        }
        panel.innerHTML = htmlPreviewAlmacen(item, 'modal');
    }

    function relocateModalNodes() {
        const $modal = $('#' + modalId);
        if ($modal.length && $modal.closest('.modal').length && !$modal.data('relocated')) {
            $modal.appendTo(document.body);
            $modal.data('relocated', true);
        }
        const hoverEl = document.getElementById('almacenHoverPreview-' + sectionId);
        if (hoverEl && hoverEl.closest('.modal') && !hoverEl.dataset.relocated) {
            document.body.appendChild(hoverEl);
            hoverEl.dataset.relocated = '1';
        }
    }

    function abrirModalBuscar(opciones) {
        relocateModalNodes();
        const $m = $('#' + modalId);
        const irMapa = opciones && opciones.mapa;

        $m.off('show.bs.modal.almacenAnidado').on('show.bs.modal.almacenAnidado', function () {
            const z = 1050 + (10 * $('.modal:visible').length);
            $(this).css('z-index', z);
            $('body').addClass('modal-almacen-anidado');
            window.setTimeout(function () {
                $('.modal-backdrop').not('.modal-stack').last()
                    .css('z-index', z - 1)
                    .addClass('modal-stack');
            }, 0);
        });

        $m.off('hidden.bs.modal.almacenAnidado').on('hidden.bs.modal.almacenAnidado', function () {
            $('.modal-backdrop.modal-stack').last().remove();
            if ($('.modal:visible').length) {
                $('body').addClass('modal-open');
            } else {
                $('body').removeClass('modal-almacen-anidado');
            }
        });

        if (irMapa) {
            $m.one('show.bs.modal', function () {
                $(this).trigger('show.bs.modal.almacenAnidado');
            });
            $m.modal('show');
            window.setTimeout(function () {
                $('#' + modalId + '-tab-mapa').tab('show');
            }, 150);
        } else {
            $m.one('show.bs.modal', function () {
                $(this).trigger('show.bs.modal.almacenAnidado');
            });
            $('#' + modalId + '-tab-lista').tab('show');
            $m.modal('show');
        }

        $m.one('hidden.bs.modal', function () {
            $(this).trigger('hidden.bs.modal.almacenAnidado');
        });
    }

    function posicionarHoverPreview(e, hoverEl) {
        if (!hoverEl) return;
        const pad = 14;
        const maxW = 320;
        const maxH = 200;
        let x = e.clientX + pad;
        let y = e.clientY + pad;
        if (x + maxW > window.innerWidth - 8) x = e.clientX - maxW - pad;
        if (y + maxH > window.innerHeight - 8) y = e.clientY - maxH - pad;
        hoverEl.style.left = Math.max(8, x) + 'px';
        hoverEl.style.top = Math.max(8, y) + 'px';
    }

    function bindHoverPreview() {
        const hoverEl = document.getElementById('almacenHoverPreview-' + sectionId);
        if (!hoverEl) return;

        function mostrar(item, e, modo) {
            hoverEl.innerHTML = htmlPreviewAlmacen(item, modo || 'modal');
            hoverEl.classList.remove('almacen-hover-preview--breve', 'almacen-hover-preview--mapa');
            if (modo === 'mapa') {
                hoverEl.classList.add('almacen-hover-preview--mapa');
            }
            hoverEl.style.display = 'block';
            hoverEl.setAttribute('aria-hidden', 'false');
            posicionarHoverPreview(e, hoverEl);
        }

        function ocultar() {
            hoverEl.style.display = 'none';
            hoverEl.classList.remove('almacen-hover-preview--mapa');
            hoverEl.setAttribute('aria-hidden', 'true');
        }

        $('#' + sectionId).on('mouseenter', '.almacen-card', function (e) {
            if ($(this).closest('.almacen-destacados-grid.d-none').length) return;
            mostrar(itemDesdeCard($(this)), e, 'modal');
        }).on('mousemove', '.almacen-card', function (e) {
            if (hoverEl.style.display === 'block') posicionarHoverPreview(e, hoverEl);
        }).on('mouseleave', '.almacen-card', ocultar);

        $(document).on('mouseenter', '#' + modalId + '-lista-items .almacen-modal-item', function (e) {
            const id = $(this).data('id');
            const item = almacenPorId(id);
            mostrar(item, e, 'modal');
            actualizarPreviewPanelModal(item);
        });
        $(document).on('mousemove', '#' + modalId + '-lista-items .almacen-modal-item', function (e) {
            if (hoverEl.style.display === 'block') posicionarHoverPreview(e, hoverEl);
        });
        $(document).on('mouseleave', '#' + modalId + '-lista-items .almacen-modal-item', ocultar);
    }

    function actualizarResumenDestino(id) {
        if (!resumenDestinoId) return;
        const el = document.getElementById(resumenDestinoId);
        if (!el) return;

        const item = almacenPorId(id);
        if (!item) {
            el.innerHTML = '<div class="alert alert-info mb-0 py-3"><i class="fas fa-warehouse mr-1"></i> Elija una tarjeta sugerida o use <strong>Buscar todos</strong>.</div>';
            return;
        }

        const ocupado = Math.max(0, (item.capacidad || 0) - (item.disponible || 0));
        const porcentaje = item.capacidad > 0 ? (ocupado / item.capacidad) * 100 : 0;
        const fillClass = barraClaseResumen(porcentaje);
        const ubicacion = item.direccion || item.ubicacion || '';
        const cantidadTxt = cantidadFija ? (cantidadFija + ' kg') : '';

        el.innerHTML =
            '<div class="border rounded p-3 bg-light">' +
                '<div class="d-flex flex-wrap justify-content-between align-items-start mb-2" style="gap:.5rem;">' +
                    '<div>' +
                        '<span class="badge badge-success mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Destino de envío</span>' +
                        '<h6 class="font-weight-bold mb-1"><i class="fas fa-warehouse text-warning mr-1"></i>' + (item.nombre || '') + '</h6>' +
                        '<p class="small text-muted mb-0">' + (item.tipo || 'General') + (ubicacion ? ' · ' + ubicacion : '') + '</p>' +
                    '</div>' +
                    (cantidadTxt ? '<div class="text-right"><span class="text-muted small d-block">Cantidad a ingresar</span><strong class="text-dark">' + cantidadTxt + '</strong></div>' : '') +
                '</div>' +
                '<div class="small">' +
                    '<span class="text-success font-weight-bold">' + Math.round(item.disponible || 0).toLocaleString() + '</span>' +
                    '<span class="text-muted">/ ' + Math.round(item.capacidad || 0).toLocaleString() + ' kg disponibles</span>' +
                    '<span class="text-muted">(' + porcentaje.toFixed(1) + ' % ocupado)</span>' +
                '</div>' +
                '<div class="capacidad-bar mt-1"><div class="fill ' + fillClass + '" style="width:' + Math.min(porcentaje, 100) + '%"></div></div>' +
                '<p class="small text-muted mb-0 mt-2"><i class="fas fa-info-circle mr-1"></i> Destino actualizado. Confirme o vuelva a cambiar.</p>' +
            '</div>';
    }

    function aplicarSeleccion(id, nombre, disponible, card) {
        $('#' + sectionId + ' .almacen-card').removeClass('selected').css({ background: 'white', borderColor: '#dee2e6' });
        $('#' + sectionId + ' .almacen-card .fa-check-circle').hide();

        const cardsSel = cardPorId(id);
        if (cardsSel.length) {
            cardsSel.addClass('selected');
            cardsSel.find('.fa-check-circle').show();
        } else if (card && card.length) {
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
        actualizarProyeccionTarjetas();
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
                contenedorAlertas().prepend(
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
                contenedorAlertas().prepend(
                    '<div id="alertaCapacidad-' + sectionId + '" class="alert alert-danger p-2 small mb-2">' +
                    '⚠️ Excede capacidad: ' + cantidad + ' ' + umProduccion + ' &gt; disp. ' +
                    disponible + ' ' + umAlmacen + '</div>'
                );
            }
        } else {
            $('#alertaCapacidad-' + sectionId).remove();
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
            actualizarPreviewPanelModal(null);
            return;
        }

        sinRes.addClass('d-none');
        resumen.text(items.length + ' almacén' + (items.length === 1 ? '' : 'es') + ' encontrado' + (items.length === 1 ? '' : 's'));

        let previewItem = null;
        items.forEach(function (item) {
            const sel = String(seleccionado) === String(item.id) ? ' is-selected' : '';
            if (sel) {
                previewItem = item;
            }
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
                            '<span class="text-success font-weight-bold">' + Number(item.disponible).toLocaleString('es-BO') + '</span>' +
                            '<span class="text-muted"> / ' + Number(item.capacidad).toLocaleString('es-BO') + ' ' + (item.um || 'kg') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div><i class="fas fa-chevron-right text-muted"></i></div>' +
                '</div>';
            cont.append(html);
        });

        actualizarPreviewPanelModal(previewItem || items[0]);
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
        $('#' + modalId + '-lista-items .almacen-modal-item').filter(function () {
            return String($(this).data('id')) === String(id);
        }).addClass('is-selected');
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
                .bindTooltip(item.nombre, {
                    className: 'almacen-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -14],
                })
                .addTo(mapaState.capa);

            marker._almacenId = item.id;
            marker._almacenItem = item;
            marker.on('mouseover', function (e) {
                this.openTooltip();
                const hoverEl = document.getElementById('almacenHoverPreview-' + sectionId);
                if (hoverEl && e.originalEvent) {
                    hoverEl.innerHTML = htmlPreviewAlmacen(item, 'mapa');
                    hoverEl.classList.remove('almacen-hover-preview--breve');
                    hoverEl.classList.add('almacen-hover-preview--mapa');
                    hoverEl.style.display = 'block';
                    hoverEl.setAttribute('aria-hidden', 'false');
                    posicionarHoverPreview(e.originalEvent, hoverEl);
                }
            });
            marker.on('mousemove', function (e) {
                const hoverEl = document.getElementById('almacenHoverPreview-' + sectionId);
                if (hoverEl && hoverEl.style.display === 'block' && e.originalEvent) {
                    posicionarHoverPreview(e.originalEvent, hoverEl);
                }
            });
            marker.on('mouseout', function () {
                const hoverEl = document.getElementById('almacenHoverPreview-' + sectionId);
                if (hoverEl) {
                    hoverEl.style.display = 'none';
                    hoverEl.classList.remove('almacen-hover-preview--mapa');
                    hoverEl.setAttribute('aria-hidden', 'true');
                }
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

    function aplicarFiltroDestacados(orden) {
        $('#' + sectionId + ' .almacen-destacados-grid').addClass('d-none');
        $('#' + sectionId + ' .almacen-destacados-grid[data-orden="' + orden + '"]').removeClass('d-none');

        const hint = document.getElementById('almacen-destacados-hint-' + sectionId);
        if (hint) {
            const etiqueta = orden === 'menos' ? 'menos usados' : 'más usados';
            const count = $('#' + sectionId + ' .almacen-destacados-grid[data-orden="' + orden + '"] .almacen-card').length;
            hint.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Se muestran los ' + count + ' almacenes agrícolas <strong>' + etiqueta + '</strong>.';
        }

        const id = $('#' + hiddenInputId).val();
        if (id) {
            const card = cardPorId(id).filter(function () {
                return $(this).closest('.almacen-destacados-grid:not(.d-none)').length > 0;
            });
            $('#' + sectionId + ' .almacen-card').removeClass('selected').css({ background: 'white', borderColor: '#dee2e6' });
            $('#' + sectionId + ' .almacen-card .fa-check-circle').hide();
            if (card.length) {
                card.addClass('selected');
                card.find('.fa-check-circle').show();
            }
        }
        actualizarProyeccionTarjetas();
    }

    window.AlmacenEnvio = window.AlmacenEnvio || {};
    window.AlmacenEnvio.recomendar = recomendarAlmacen;
    window.AlmacenEnvio.seleccionar = function (id) {
        seleccionarAlmacenPorId(id, false);
    };

    $(function () {
        if (requiereAlmacen && !$('#' + hiddenInputId).val()) {
            const cards = cardsEnSeccion();
            if (cards.length) {
                cards.first().trigger('click');
            }
        }

        const seleccionInicial = $('#' + hiddenInputId).val();
        if (seleccionInicial) {
            const item = almacenPorId(seleccionInicial);
            if (item) actualizarSeleccionExterna(seleccionInicial, item.nombre);
            actualizarResumenDestino(seleccionInicial);
            actualizarProyeccionTarjetas();
        }

        if (productoHint) {
            recomendarAlmacen(productoHint);
        }

        relocateModalNodes();
        bindHoverPreview();

        if (formSelector && requiereAlmacen) {
            $(formSelector).on('submit', function (e) {
                if (!$('#' + hiddenInputId).val()) {
                    e.preventDefault();
                    alert('Debe seleccionar un almacén antes de continuar.');
                    document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        $('#' + sectionId).on('click', '.almacen-card', function () {
            const $card = $(this);
            if ($card.closest('.almacen-destacados-grid.d-none').length) return;
            aplicarSeleccion(
                $card.data('id'),
                $card.data('nombre'),
                $card.data('disponible'),
                $card
            );
        });

        $('#' + sectionId).on('keydown', '.almacen-card', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        if (cantidadFija === null) {
            $('#' + cantidadInputId + ', #' + unidadSelectId).on('change keyup', function () {
                const cantidad = cantidadActual();
                const id = $('#' + hiddenInputId).val();
                const card = cardPorId(id).filter(function () {
                    return $(this).closest('.almacen-destacados-grid:not(.d-none), #almacenesContainer-' + sectionId).length > 0;
                }).first();
                if (card.length) {
                    verificarCapacidad(cantidad, card.data('disponible'), card);
                } else if (id) {
                    const item = almacenPorId(id);
                    if (item) verificarCapacidadExterna(cantidad, item.disponible, id);
                }
                actualizarProyeccionTarjetas();
            });
        } else {
            const card = cardsEnSeccion().filter('.selected');
            const cantidad = cantidadActual();
            if (card.length && cantidad > 0) {
                verificarCapacidad(cantidad, card.data('disponible'), card);
            }
        }

        if (almacenesCatalogo.length && $('#' + modalId).length) {
            $('#' + modalId).on('shown.bs.modal', function () {
                aplicarFiltrosModal();
            });

            $('#' + modalId + '-filtro-nombre, #' + modalId + '-filtro-cap-min, #' + modalId + '-filtro-cap-max')
                .on('input change', aplicarFiltrosModal);

            $(document).on('click', '#' + modalId + '-lista-items .almacen-modal-item', function () {
                const id = $(this).data('id');
                actualizarPreviewPanelModal(almacenPorId(id));
                seleccionarAlmacenPorId(id, true);
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
                abrirModalBuscar();
            });

            $(document).on('click', '.btn-buscar-almacenes[data-section="' + sectionId + '"]', function () {
                abrirModalBuscar();
            });

            $(document).on('click', '.btn-ver-mapa-almacenes[data-section="' + sectionId + '"]', function () {
                abrirModalBuscar({ mapa: true });
            });

            $(document).on('click', '.almacen-destacados-filtro [data-filtro][data-section="' + sectionId + '"]', function () {
                const orden = $(this).data('filtro');
                const grupo = $(this).closest('.almacen-destacados-filtro');
                grupo.find('[data-filtro]').removeClass('btn-success active').addClass('btn-outline-success');
                $(this).removeClass('btn-outline-success').addClass('btn-success active');
                aplicarFiltroDestacados(orden);
            });
        }

        actualizarProyeccionTarjetas();
    });
})(jQuery);
</script>
