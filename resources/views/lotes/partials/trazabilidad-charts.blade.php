@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const porFase = @json($chart_por_fase ?? ['labels' => [], 'data' => [], 'colors' => []]);
    const porTipo = @json($chart_por_tipo ?? ['labels' => [], 'data' => []]);
    const linea = @json($chart_linea ?? ['labels' => [], 'data' => []]);

    const defaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
    };

    const elFase = document.getElementById('chartTrazFases');
    if (elFase && porFase.labels.length) {
        new Chart(elFase, {
            type: 'doughnut',
            data: {
                labels: porFase.labels,
                datasets: [{ data: porFase.data, backgroundColor: porFase.colors, borderWidth: 2 }],
            },
            options: { ...defaults, cutout: '58%' },
        });
    }

    const elTipo = document.getElementById('chartTrazTipos');
    if (elTipo && porTipo.labels.length) {
        new Chart(elTipo, {
            type: 'bar',
            data: {
                labels: porTipo.labels,
                datasets: [{
                    label: 'Eventos',
                    data: porTipo.data,
                    backgroundColor: 'rgba(44, 85, 48, 0.75)',
                    borderRadius: 6,
                }],
            },
            options: {
                ...defaults,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { ...defaults.plugins, legend: { display: false } },
            },
        });
    }

    const elLinea = document.getElementById('chartTrazLinea');
    if (elLinea && linea.labels.length) {
        new Chart(elLinea, {
            type: 'line',
            data: {
                labels: linea.labels,
                datasets: [{
                    label: 'Eventos registrados',
                    data: linea.data,
                    borderColor: '#2c5530',
                    backgroundColor: 'rgba(44, 85, 48, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                }],
            },
            options: {
                ...defaults,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }
});
</script>
@endpush
