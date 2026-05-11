/**
 * dashboard.js  —  Chart.js for MMB POS Dashboard
 * Data injected via window.dashboardData from PHP.
 */
(function () {

  /* ─── 1. Register the value-label plugin BEFORE creating the chart ─── */
  Chart.register({
    id: 'barValueLabels',
    afterDatasetsDraw(chart) {
      const { ctx, data } = chart;
      chart.getDatasetMeta(0).data.forEach((bar, i) => {
        const value = data.datasets[0].data[i];
        if (!value || value === 0) return;
        const label = value >= 1000
          ? '₱' + (value / 1000).toFixed(1) + 'k'
          : '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 0 });
        ctx.save();
        ctx.fillStyle    = '#c0392b';
        ctx.font         = '700 10px Inter, sans-serif';
        ctx.textAlign    = 'center';
        ctx.textBaseline = 'bottom';
        ctx.fillText(label, bar.x, bar.y - 3);
        ctx.restore();
      });
    }
  });

  /* ─── 2. Data ─── */
  const rawData   = window.dashboardData?.monthlySalesTrend ?? Array(12).fill(0);
  const salesData = Array.isArray(rawData)
    ? rawData
    : Array.from({ length: 12 }, (_, i) => rawData[i + 1] ?? 0);
  const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

  const canvas = document.getElementById('salesChart');
  if (!canvas) return;
  const chartCtx = canvas.getContext('2d');

  /* ─── 3. Gradient ─── */
  function makeGradient(ctx, chartArea) {
    const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    g.addColorStop(0,   'rgba(192, 57,  43, 1.00)');
    g.addColorStop(0.5, 'rgba(231, 76,  60, 0.85)');
    g.addColorStop(1,   'rgba(231, 76,  60, 0.50)');
    return g;
  }
  let cachedGrad, gW, gH;

  /* ─── 4. Chart ─── */
  new Chart(chartCtx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Net Sales',
        data: salesData,
        backgroundColor: 'rgba(192, 57, 43, 0.85)',
        hoverBackgroundColor: '#a93226',
        borderColor:   'transparent',
        borderWidth:    0,
        borderRadius:  { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
        borderSkipped: false,
      }]
    },
    options: {
      responsive:          true,
      maintainAspectRatio: false,
      animation:           { duration: 900, easing: 'easeOutQuart' },
      layout: { padding: { top: 24 } },   // room for value labels
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1a2535',
          titleColor:      '#fff',
          bodyColor:       '#fca5a5',
          padding:          14,
          cornerRadius:     10,
          displayColors:    false,
          callbacks: {
            title: (items) => labels[items[0].dataIndex],
            label: (ctx)   => '  ₱ ' + Number(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid:   { color: 'rgba(0,0,0,.04)', drawBorder: false },
          border: { display: false },
          ticks:  {
            color:    '#94a3b8',
            font:     { size: 11, family: 'Inter' },
            callback: (v) => v >= 1000 ? '₱' + (v / 1000).toFixed(0) + 'k' : '₱' + v,
          }
        },
        x: {
          grid:   { display: false },
          border: { display: false },
          ticks:  { color: '#94a3b8', font: { size: 11, family: 'Inter' } }
        }
      }
    }
  });

})();
