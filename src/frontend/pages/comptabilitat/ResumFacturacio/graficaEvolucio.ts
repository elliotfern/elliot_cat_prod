import Chart from 'chart.js/auto';

export interface EvolucioMensual {
  mes: number;
  ingressos: number;
  despeses: number;
  resultat: number;
}

const nomsMesos = ['Gen', 'Feb', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Des'];

export function renderGraficaEvolucio(container: HTMLElement, mesos: EvolucioMensual[]): void {
  container.innerHTML = `
    <div class="card mb-4">
      <div class="card-body">
        <h2 class="h5 mb-4">
          Evolució mensual
        </h2>

        <div style="position: relative; height: 350px;">
          <canvas id="graficaEvolucioCanvas"></canvas>
        </div>
      </div>
    </div>
  `;

  const canvas = document.getElementById('graficaEvolucioCanvas') as HTMLCanvasElement | null;

  if (!canvas) {
    return;
  }

  new Chart(canvas, {
    type: 'line',

    data: {
      labels: nomsMesos,

      datasets: [
        {
          label: 'Ingressos',
          data: mesos.map((item) => item.ingressos),
          tension: 0.3,
        },
        {
          label: 'Despeses',
          data: mesos.map((item) => item.despeses),
          tension: 0.3,
        },
        {
          label: 'Resultat',
          data: mesos.map((item) => item.resultat),
          tension: 0.3,
        },
      ],
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,

      plugins: {
        legend: {
          position: 'top',
        },

        tooltip: {
          callbacks: {
            label: (context) => {
              const value = context.parsed.y ?? 0;

              return `${context.dataset.label}: ${value.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
              })} €`;
            },
          },
        },
      },

      scales: {
        y: {
          ticks: {
            callback: (value) => {
              return `${Number(value).toLocaleString('es-ES')} €`;
            },
          },
        },
      },
    },
  });
}
