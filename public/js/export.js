
// export.js
document.addEventListener('DOMContentLoaded', function() {
    const exportCsvBtn = document.getElementById('export-csv');
    const exportPdfBtn = document.getElementById('export-pdf');

    function downloadFile(filename, content, type) {
        const blob = new Blob([content], { type });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
    }

    exportCsvBtn?.addEventListener('click', () => {
        const rows = Array.from(document.querySelectorAll('#report-table tr')).map(tr => 
            Array.from(tr.querySelectorAll('th,td')).map(td => td.innerText).join(',')
        ).join('\n');
        downloadFile('reporte.csv', rows, 'text/csv');
    });

    exportPdfBtn?.addEventListener('click', async () => {
        const chartCanvas = document.getElementById('report-chart');
        let chartImg = chartCanvas ? chartCanvas.toDataURL('image/png') : '';
        const filters = document.getElementById('filters').innerText;

        const res = await fetch('/app/controllers/ReportController.php?action=exportPdf', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chart: chartImg, filters: filters })
        });
        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'reporte.pdf';
        a.click();
    });
});
