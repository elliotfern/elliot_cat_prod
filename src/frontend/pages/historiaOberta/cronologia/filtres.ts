import { state } from '../../../types/Esdeveniment';
import { loadEventos, loadSubetapas } from './llistatEsdeveniments';

const etapas = [
  { id: 1, nombre: 'Prehistòria' },
  { id: 2, nombre: 'Edat Antiga' },
  { id: 3, nombre: 'Edat Mitjana' },
  { id: 4, nombre: 'Edat Moderna' },
  { id: 5, nombre: 'Edat Contemporània' },
  { id: 6, nombre: 'Món Actual' },
];

export function renderFiltros(container: HTMLElement) {
  const wrapper = document.createElement('div');

  wrapper.innerHTML = `
    <h4>Selecciona una Etapa Històrica:</h4>
    <div class="d-flex flex-wrap gap-2 mb-3" id="etapas"></div>
    <div id="subetapas-container" class="mb-3"></div>
  `;

  const etapasDiv = wrapper.querySelector('#etapas')!;

  const buttons: HTMLButtonElement[] = []; // 👈 importante

  etapas.forEach((e) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-primary';
    btn.textContent = e.nombre;

    btn.onclick = () => {
      state.etapa = e.id;
      state.subetapa = null;

      // reset visual etapas
      buttons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      loadSubetapas();
      loadEventos();
    };

    buttons.push(btn);
    etapasDiv.appendChild(btn);
  });

  container.appendChild(wrapper);
}

export function setActiveEtapaUI() {
  document.querySelectorAll('#etapas button').forEach((btn, index) => {
    const etapaId = index + 1;

    if (etapaId === state.etapa) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });
}
