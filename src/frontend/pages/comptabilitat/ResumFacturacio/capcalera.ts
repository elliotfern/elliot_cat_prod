import { formatData } from '../../../utils/formataData';

export interface Emissor {
  id: string;
  nom: string;
  nif: string;
  numero_iva: string | null;
  pais_ca: string | null;
  adreca: string | null;
  telefon: string | null;
  email: string | null;
  pais_id: string;
  dataInici: string;
  dataFi: string;
}

export interface EmissorSelector {
  id: string;
  nom: string;
  dataInici: string;
  dataFi: string;
}

export interface SelectorComptabilitat {
  emissors: EmissorSelector[];
  emissorSeleccionat: string;
  exercicis: number[];
  exerciciSeleccionat: number;
}

export function renderCapcalera(container: HTMLElement, emissor: Emissor, selector: SelectorComptabilitat): void {
  container.innerHTML = `
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">

      <div>
        <h1 class="h3 mb-1">
          Comptabilitat
        </h1>

        <p id="nomEmissorCapcalera"
            class="text-body-secondary mb-0"
            >
            ${emissor.nom}
            </p>
      </div>

      <div class="d-flex flex-column flex-sm-row gap-3 mt-3 mt-md-0">

        <div>
          <label
            for="selectEmissor"
            class="form-label small text-body-secondary mb-1"
          >
            Emissor
          </label>

          <select
            id="selectEmissor"
            class="form-select"
          >
            ${selector.emissors
              .map(
                (item) => `
                  <option
                    value="${item.id}"
                    ${item.id === selector.emissorSeleccionat ? 'selected' : ''}
                  >
                    ${item.nom}
                  </option>
                `
              )
              .join('')}
          </select>
        </div>

        <div>
          <label
            for="selectExercici"
            class="form-label small text-body-secondary mb-1"
          >
            Exercici
          </label>

          <select
            id="selectExercici"
            class="form-select"
          >
            ${selector.exercicis
              .map(
                (exercici) => `
                  <option
                    value="${exercici}"
                    ${exercici === selector.exerciciSeleccionat ? 'selected' : ''}
                  >
                    ${exercici}
                  </option>
                `
              )
              .join('')}
          </select>
        </div>

      </div>

    </div>

    <div class="card mb-4">

      <div class="card-body">

        <div class="row g-3">

          <div class="col-md-6">
            <div class="small text-body-secondary">
              Estructura comptable
            </div>

             <div id="nomEstructuraComptable" class="fw-semibold">
            ${emissor.nom}
            </div>
          
          </div>

          <div class="col-md-2">
            <div class="small text-body-secondary">
              Estat
            </div>

            <span
                id="estatEmissor"
                class="badge ${emissor.dataFi === '9999-12-31' ? 'text-bg-success' : 'text-bg-secondary'}"
                >
                ${emissor.dataFi === '9999-12-31' ? 'Activa' : 'Arxivada'}
                </span>
          </div>

            <div class="col-md-2">
                <div class="small text-body-secondary">
                    Inici activitat
                </div>

                <div
                    id="dataIniciActivitat"
                    class="fw-semibold"
                >
                    ${formatData(emissor.dataInici)}
                </div>
                </div>

                <div class="col-md-2">
                <div class="small text-body-secondary">
                    Fi activitat
                </div>

                <div
                    id="dataFiActivitat"
                    class="fw-semibold"
                >
                    ${emissor.dataFi === '9999-12-31' ? '—' : formatData(emissor.dataFi)}
                </div>
            </div>

      </div>

    </div>
  `;
}

export function onCanviEmissor(callback: (emissorId: string) => void): void {
  const select = document.getElementById('selectEmissor') as HTMLSelectElement;

  if (!select) {
    return;
  }

  select.addEventListener('change', () => {
    callback(select.value);
  });
}

export function actualizarExercicis(exercicis: number[], exerciciSeleccionat: number): void {
  const select = document.getElementById('selectExercici') as HTMLSelectElement;

  if (!select) {
    return;
  }

  select.innerHTML = exercicis
    .map(
      (exercici) => `
        <option
          value="${exercici}"
          ${exercici === exerciciSeleccionat ? 'selected' : ''}
        >
          ${exercici}
        </option>
      `
    )
    .join('');
}

export function onCanviExercici(callback: (exercici: number) => void): void {
  const select = document.getElementById('selectExercici') as HTMLSelectElement;

  if (!select) {
    return;
  }

  select.addEventListener('change', () => {
    callback(Number(select.value));
  });
}

export function actualizarCapcalera(emissor: Emissor): void {
  const nomEmissorCapcalera = document.getElementById('nomEmissorCapcalera');

  const nomEstructuraComptable = document.getElementById('nomEstructuraComptable');

  const dataIniciActivitat = document.getElementById('dataIniciActivitat');

  const dataFiActivitat = document.getElementById('dataFiActivitat');

  const estatEmissor = document.getElementById('estatEmissor');

  if (!nomEmissorCapcalera || !nomEstructuraComptable || !dataIniciActivitat || !dataFiActivitat || !estatEmissor) {
    return;
  }

  nomEmissorCapcalera.textContent = emissor.nom;
  nomEstructuraComptable.textContent = emissor.nom;
  dataIniciActivitat.textContent = formatData(emissor.dataInici);
  dataFiActivitat.textContent = emissor.dataFi === '9999-12-31' ? '—' : formatData(emissor.dataFi);

  if (emissor.dataFi === '9999-12-31') {
    estatEmissor.textContent = 'Activa';
    estatEmissor.className = 'badge text-bg-success';
  } else {
    estatEmissor.textContent = 'Arxivada';
    estatEmissor.className = 'badge text-bg-secondary';
  }
}
