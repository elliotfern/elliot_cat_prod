import { state } from '../../../types/Esdeveniment';
import { loadEventos } from './llistatEsdeveniments';

interface Subetapa {
  id: string;
  nomSubEtapa: string;
}

export function renderSubetapas(container: HTMLElement, subetapas: Subetapa[]): void {
  container.innerHTML = '';

  if (subetapas.length === 0) {
    container.innerHTML = `<p>No hi ha subetapes disponibles</p>`;
    return;
  }

  const title = document.createElement('h4');
  title.textContent = 'Selecciona una Subetapa:';
  container.appendChild(title);

  const wrapper = document.createElement('div');
  wrapper.className = 'd-flex flex-wrap gap-2';

  subetapas.forEach((sub) => {
    const btn = document.createElement('button');
    btn.className = 'btn btn-outline-secondary';
    btn.textContent = sub.nomSubEtapa;

    btn.onclick = () => {
      state.subetapa = sub.id;

      wrapper.querySelectorAll('button').forEach((b) => {
        b.classList.remove('active');
      });

      btn.classList.add('active');

      loadEventos();
    };

    wrapper.appendChild(btn);
  });

  container.appendChild(wrapper);
}
