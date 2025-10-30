document.addEventListener("DOMContentLoaded", () => {
  const ctx = document.getElementById('weeklyChart');
  if (!ctx) return; // safety check biar gak error kalau canvas belum ada

  new Chart(ctx.getContext('2d'), {
    type: 'line',
    data: {
      labels: chartData.labels,
      datasets: [
        {
          label: 'Hadir',
          data: chartData.hadir,
          borderColor: '#4F46E5',
          borderWidth: 2,
          fill: false,
          tension: 0.3
        },
        {
          label: 'Sakit',
          data: chartData.sakit,
          borderColor: '#EF4444',
          borderWidth: 2,
          fill: false,
          tension: 0.3
        },
        {
          label: 'Izin',
          data: chartData.izin,
          borderColor: '#F59E0B',
          borderWidth: 2,
          fill: false,
          tension: 0.3
        },
        {
          label: 'Alpha',
          data: chartData.alpha,
          borderColor: '#9CA3AF',
          borderWidth: 2,
          fill: false,
          tension: 0.3
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    }
  });
});
