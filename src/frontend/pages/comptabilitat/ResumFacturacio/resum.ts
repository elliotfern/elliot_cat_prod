import { api } from '../../../Infrastructure/Api/Client/ApiClient';
import { formatEuro } from '../../../utils/locales/formatEuro';

export interface ResumComptabilitat {
  exercici: number;
  ingressos: number;
  despeses: number;
  resultat: number;
}

export interface ResumComptabilitat {
  exercici: number;
  ingressos: number;
  despeses: number;
  resultat: number;
}

export function renderResum(container: HTMLElement, resum: ResumComptabilitat): void {
  container.innerHTML = `
    <div class="row g-4 mb-4">

      <div class="col-12 col-md-4">
        <div class="card h-100">
          <div class="card-body">

            <div class="small text-body-secondary mb-1">
              Ingressos
            </div>

            <div class="text-body-secondary small mb-3">
              Facturats
            </div>

            <div class="fs-3 fw-semibold">
              ${formatEuro(resum.ingressos)}
            </div>

          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card h-100">
          <div class="card-body">

            <div class="small text-body-secondary mb-1">
              Despeses
            </div>

            <div class="text-body-secondary small mb-3">
              Pagades
            </div>

            <div class="fs-3 fw-semibold">
              ${formatEuro(resum.despeses)}
            </div>

          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card h-100">
          <div class="card-body">

            <div class="small text-body-secondary mb-1">
              Resultat
            </div>

            <div class="text-body-secondary small mb-3">
              Ingressos - Despeses
            </div>

            <div class="fs-3 fw-semibold">
              ${formatEuro(resum.resultat)}
            </div>

          </div>
        </div>
      </div>

    </div>
  `;
}

export async function getResum(emissorId: string, exercici: number): Promise<ResumComptabilitat> {
  return api.get<ResumComptabilitat>('comptabilitat/get/resum', {
    emissor_id: emissorId,
    exercici,
  });
}
