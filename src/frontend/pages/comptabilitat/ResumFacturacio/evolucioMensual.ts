import { formatEuro } from '../../../utils/locales/formatEuro';

export interface EvolucioMensual {
  mes: number;
  ingressos: number;
  despeses: number;
  resultat: number;
}

export interface EvolucioMensualResponse {
  exercici: number;
  mesos: EvolucioMensual[];
}

export function renderEvolucioMensual(container: HTMLElement, dades: EvolucioMensual[]): void {
  container.innerHTML = `
    <div class="card mb-4">

      <div class="card-header">
        <h2 class="h5 mb-0">
          Evolució mensual
        </h2>
      </div>

      <div class="card-body p-0">

        <div class="table-responsive">

          <table class="table table-hover mb-0">

            <thead>
              <tr>
                <th>Mes</th>
                <th class="text-end">Ingressos</th>
                <th class="text-end">Despeses</th>
                <th class="text-end">Resultat</th>
              </tr>
            </thead>

            <tbody>
              ${dades
                .map(
                  (mes) => `
                <tr>
                  <td>
                    ${getNomMes(mes.mes)}
                  </td>

                  <td class="text-end">
                    ${formatEuro(mes.ingressos)}
                  </td>

                  <td class="text-end">
                    ${formatEuro(mes.despeses)}
                  </td>

                  <td class="text-end fw-semibold">
                    ${formatEuro(mes.resultat)}
                  </td>
                </tr>
              `
                )
                .join('')}
            </tbody>

          </table>

        </div>

      </div>

    </div>
  `;
}

function getNomMes(mes: number): string {
  const mesos = ['Gener', 'Febrer', 'Març', 'Abril', 'Maig', 'Juny', 'Juliol', 'Agost', 'Setembre', 'Octubre', 'Novembre', 'Desembre'];

  return mesos[mes - 1];
}
