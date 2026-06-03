@php
    $sectionId = $sectionId ?? 'almacenSection';
    $hiddenInputId = $hiddenInputId ?? 'almacenid';
    $formSelector = $formSelector ?? null;
    $cantidadFija = $cantidadFija ?? null;
    $cantidadInputId = $cantidadInputId ?? 'cantidad';
    $unidadSelectId = $unidadSelectId ?? 'unidadmedidaid';
    $productoHint = $productoHint ?? '';
@endphp
<script>
(function ($) {
    const sectionId = @json($sectionId);
    const hiddenInputId = @json($hiddenInputId);
    const formSelector = @json($formSelector);
    const cantidadFija = @json($cantidadFija);
    const cantidadInputId = @json($cantidadInputId);
    const unidadSelectId = @json($unidadSelectId);
    const productoHint = @json($productoHint);
    const almacenOptionsId = '#almacenOptions-' + sectionId;
    const almacenSeleccionadoId = '#almacen-seleccionado-' + sectionId;

    const factores = {
        'kg': 1, 'kilogramo': 1, 'kilogramos': 1,
        'g': 0.001, 'gr': 0.001, 'gramo': 0.001,
        't': 1000, 'ton': 1000, 'tonelada': 1000, 'toneladas': 1000,
        'qq': 46, 'quintal': 46, 'quintales': 46,
        'lb': 0.453592, 'libra': 0.453592,
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

        let mejorMatch = null;
        let maxScore = 0;
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
                if (score > maxScore) {
                    maxScore = score;
                    mejorMatch = $(this);
                }
            } else {
                $(this).css('border-color', '#dee2e6').css('background', 'white');
            }
        });

        if (mejorMatch && !$('#' + hiddenInputId).val()) {
            mejorMatch.trigger('click');
            $(almacenOptionsId + ' .alert-suggestion').remove();
            $(almacenOptionsId).prepend(
                '<div class="alert alert-info alert-dismissible fade show p-2 small mb-2 alert-suggestion" role="alert">' +
                '<i class="fas fa-lightbulb mr-1"></i> Sugerencia: <strong>' + mejorMatch.data('nombre') + '</strong>' +
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

    window.AlmacenEnvio = window.AlmacenEnvio || {};
    window.AlmacenEnvio.recomendar = recomendarAlmacen;

    $(function () {
        if (cardsEnSeccion().length === 1 && !$('#' + hiddenInputId).val()) {
            cardsEnSeccion().first().trigger('click');
        }

        if (productoHint) {
            recomendarAlmacen(productoHint);
        }

        if (formSelector) {
            $(formSelector).on('submit', function (e) {
                if (!$('#' + hiddenInputId).val()) {
                    e.preventDefault();
                    alert('Debe seleccionar un almacén antes de continuar.');
                    document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        cardsEnSeccion().on('click', function () {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const disponible = $(this).data('disponible');

            cardsEnSeccion().removeClass('selected').css({ background: 'white', borderColor: '#dee2e6' });
            cardsEnSeccion().find('.fa-check-circle').hide();

            $(this).addClass('selected');
            $(this).find('.fa-check-circle').show();
            $('#' + hiddenInputId).val(id);
            $(almacenSeleccionadoId).html('<i class="fas fa-warehouse mr-1"></i> <strong>Almacén:</strong> ' + nombre);

            const cantidad = cantidadActual();
            if (cantidad > 0) {
                verificarCapacidad(cantidad, disponible, $(this));
            }
        });

        if (cantidadFija === null) {
            $('#' + cantidadInputId).on('change keyup', function () {
                const cantidad = cantidadActual();
                const card = cardsEnSeccion().filter('.selected');
                if (card.length) {
                    verificarCapacidad(cantidad, card.data('disponible'), card);
                }
            });
        } else {
            const card = cardsEnSeccion().filter('.selected');
            const cantidad = cantidadActual();
            if (card.length && cantidad > 0) {
                verificarCapacidad(cantidad, card.data('disponible'), card);
            } else if (cantidad > 0 && cardsEnSeccion().length) {
                cardsEnSeccion().each(function () {
                    if ($(this).hasClass('selected')) {
                        verificarCapacidad(cantidad, $(this).data('disponible'), $(this));
                    }
                });
            }
        }
    });
})(jQuery);
</script>
