/* Grafik penjualan dashboard admin. Memakai Chart.js (CDN); jika offline, memakai grafik batang sederhana. */
(function () {
  const el = document.getElementById('salesChart');
  if (!el || !window.KK_CHART) return;
  const { labels, values } = window.KK_CHART;
  const rp = n => 'Rp' + Math.round(n).toLocaleString('id-ID');
  if (window.Chart) {
    new Chart(el, {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Penjualan', data: values, backgroundColor: '#E08A3C', borderRadius: 6, maxBarThickness: 36 },
        { type: 'line', label: 'Tren', data: values, borderColor: '#6F4E37', backgroundColor: 'transparent', tension: .35, pointRadius: 3 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => rp(c.parsed.y) } } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => (v / 1000) + 'rb' }, grid: { color: '#f1e8da' } }, x: { grid: { display: false } } } }
    });
  } else {
    const max = Math.max(...values, 1), wrap = document.createElement('div'); wrap.className = 'bars';
    labels.forEach((l, i) => { const d = document.createElement('div'); d.title = l + ': ' + rp(values[i]); d.innerHTML = '<i style="height:' + (values[i] / max * 90) + '%"></i><span>' + l.slice(0, 5) + '</span>'; wrap.appendChild(d); });
    el.replaceWith(wrap);
  }
})();
