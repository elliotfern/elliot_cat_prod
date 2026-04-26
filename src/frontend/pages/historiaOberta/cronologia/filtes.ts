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
    <div id="subetapas" class="mb-3"></div>
  `;

  const etapasDiv = wrapper.querySelector('#etapas')!;

  etapas.forEach((e) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-primary';
    btn.textContent = e.nombre;

    btn.onclick = () => {
      state.etapa = e.id;
      state.subetapa = null;

      loadEventos();
      loadSubetapas();

      document.querySelectorAll('#etapas button').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
    };

    etapasDiv.appendChild(btn);
  });

  container.appendChild(wrapper);
}
