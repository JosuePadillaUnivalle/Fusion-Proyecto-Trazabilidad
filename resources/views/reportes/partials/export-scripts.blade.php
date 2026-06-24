<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
(function () {
    const meta = document.getElementById('rpt-print-meta');
    const chartCfg = @json($chartConfig ?? ['type' => 'doughnut', 'labels' => [], 'values' => []]);
    const exportCfg = @json($exportConfig ?? ['headers' => [], 'rows' => [], 'sheet' => 'Reporte']);
    const kpisCfg = @json($kpisPdf ?? []);

    const chartPalettes = {
        forest: ['#1b4332', '#2d6a4f', '#40916c', '#52b788', '#74c69d'],
        teal: ['#0f4c5c', '#1a6b7c', '#2a8fa3', '#3eb5c8', '#5cc6d6'],
        navy: ['#1e3a5f', '#2c5282', '#3d6ba8', '#5a8fd4', '#7aaee0'],
        bronze: ['#6b4709', '#92610e', '#b07d14', '#c9952a', '#d4a84b'],
        wine: ['#6b2737', '#9b3848', '#b84d5f', '#d16b7c', '#e08a98'],
        indigo: ['#312e81', '#4338ca', '#5b52e0', '#7c75e8', '#9b95ef'],
    };

    const pdfHeaderRgb = {
        forest: [27, 67, 50],
        teal: [15, 76, 92],
        navy: [30, 58, 95],
        bronze: [107, 71, 9],
        wine: [107, 39, 55],
        indigo: [49, 46, 129],
    };

    const accent = meta?.dataset.accent || 'forest';
    const palette = chartPalettes[accent] || chartPalettes.forest;
    const headerRgb = pdfHeaderRgb[accent] || pdfHeaderRgb.forest;

    const canvas = document.getElementById('rptChart');
    if (canvas && chartCfg.labels && chartCfg.labels.length) {
        new Chart(canvas.getContext('2d'), {
            type: chartCfg.type || 'doughnut',
            data: {
                labels: chartCfg.labels,
                datasets: [{
                    label: chartCfg.label || 'Total',
                    data: chartCfg.values,
                    backgroundColor: palette.slice(0, chartCfg.labels.length),
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: chartCfg.type === 'bar' ? { y: { beginAtZero: true, ticks: { precision: 0 } } } : {},
            },
        });
    }

    function buildPdfDoc() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const title = meta?.dataset.title || 'Reporte';
        const periodo = meta?.dataset.periodo || '';
        const generated = meta?.dataset.generated || '';
        const filtros = meta?.dataset.filtros || '';

        doc.setFontSize(16);
        doc.setTextColor(headerRgb[0], headerRgb[1], headerRgb[2]);
        doc.text(title, 14, 18);
        doc.setFontSize(10);
        doc.setTextColor(71, 85, 105);
        let y = 26;
        if (periodo) { doc.text('Período: ' + periodo, 14, y); y += 6; }
        if (filtros) { doc.text('Filtros: ' + filtros, 14, y); y += 6; }
        doc.text('Generado: ' + generated, 14, y);
        y += 4;

        if (kpisCfg.length) {
            doc.setFontSize(9);
            doc.setTextColor(headerRgb[0], headerRgb[1], headerRgb[2]);
            const kpiText = kpisCfg.map(function (k) { return k.label + ': ' + k.value; }).join('  |  ');
            const lines = doc.splitTextToSize(kpiText, 182);
            doc.text(lines, 14, y + 8);
            y += 8 + (lines.length * 5);
        }

        doc.autoTable({
            startY: y + 6,
            head: [exportCfg.headers || []],
            body: exportCfg.rows || [],
            theme: 'striped',
            styles: { fontSize: 9, textColor: [30, 41, 59] },
            headStyles: { fillColor: headerRgb, textColor: [255, 255, 255] },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });

        return doc;
    }

    window.rptPreviewPdf = function () {
        const doc = buildPdfDoc();
        const frame = document.getElementById('rptPdfPreviewFrame');
        const titulo = document.getElementById('modalRptPdfTitulo');
        if (titulo) {
            titulo.innerHTML = '<i class="fas fa-file-pdf mr-2"></i>' + (meta?.dataset.title || 'Vista previa del reporte');
        }
        if (frame) {
            frame.src = doc.output('bloburl');
        }
        if (window.jQuery) {
            window.jQuery('#modalRptPdfPreview').modal('show');
        }
    };

    window.rptExportPdf = function () {
        const doc = buildPdfDoc();
        doc.save((exportCfg.filename || 'reporte') + '_' + new Date().toISOString().slice(0, 10) + '.pdf');
    };

    window.rptExportExcel = function () {
        const data = [exportCfg.headers || []].concat(exportCfg.rows || []);
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, exportCfg.sheet || 'Reporte');
        XLSX.writeFile(wb, (exportCfg.filename || 'reporte') + '_' + new Date().toISOString().slice(0, 10) + '.xlsx');
    };

    if (window.jQuery) {
        window.jQuery('#modalRptPdfPreview').on('hidden.bs.modal', function () {
            const frame = document.getElementById('rptPdfPreviewFrame');
            if (frame) frame.src = 'about:blank';
        });
    }
})();
</script>
