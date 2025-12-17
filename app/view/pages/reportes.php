<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reportes ENYOOI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-[Poppins]">
  <div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Reportes Administrativos</h1>
    
    <div id="filters" class="mb-4">
      <label class="mr-2">Fecha desde:</label>
      <input type="date" class="border p-1">
      <label class="ml-4 mr-2">Fecha hasta:</label>
      <input type="date" class="border p-1">
    </div>

    <table id="report-table" class="min-w-full bg-white border">
      <thead>
        <tr>
          <th class="px-4 py-2 border">ID</th>
          <th class="px-4 py-2 border">Usuario</th>
          <th class="px-4 py-2 border">Monto</th>
          <th class="px-4 py-2 border">Fecha</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="border px-4 py-2">1</td><td class="border px-4 py-2">Alice</td><td class="border px-4 py-2">10.00</td><td class="border px-4 py-2">2025-08-01</td></tr>
        <tr><td class="border px-4 py-2">2</td><td class="border px-4 py-2">Bob</td><td class="border px-4 py-2">25.50</td><td class="border px-4 py-2">2025-08-05</td></tr>
        <tr><td class="border px-4 py-2">3</td><td class="border px-4 py-2">Charlie</td><td class="border px-4 py-2">5.75</td><td class="border px-4 py-2">2025-08-10</td></tr>
      </tbody>
    </table>

    <canvas id="report-chart" class="my-6"></canvas>

    <button id="export-csv" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Exportar CSV</button>
    <button id="export-pdf" class="bg-green-500 text-white px-4 py-2 rounded">Exportar PDF</button>
  </div>

  <script src="public/assets/js/export.js"></script>
  <script>
  const ctx = document.getElementById('report-chart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Alice', 'Bob', 'Charlie'],
      datasets: [{
        label: 'Montos',
        data: [10, 25.5, 5.75],
        backgroundColor: ['#3b82f6','#10b981','#f59e0b']
      }]
    }
  });
  </script>
</body>
</html>
