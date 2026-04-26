import { state } from '../../../types/Esdeveniment';
import { pintarTabla } from './taula';

export function renderPaginacion(container: HTMLElement) {
  const totalPaginas = Math.ceil(state.eventos.length / state.eventosPorPagina);

  if (totalPaginas <= 1) return;

  const nav = document.createElement('div');
  nav.className = 'd-flex flex-wrap gap-2 mt-3';

  for (let i = 1; i <= totalPaginas; i++) {
    const btn = document.createElement('button');
    btn.className = 'btn btn-sm btn-outline-primary';
    btn.textContent = i.toString();

    if (i === state.paginaActual) {
      btn.classList.add('active');
    }

    btn.onclick = () => {
      state.paginaActual = i;
      pintarTabla();

      // refrescar botones activos
      container.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      window.scrollTo({
        top: container.offsetTop,
        behavior: 'smooth',
      });
    };

    nav.appendChild(btn);
  }

  container.appendChild(nav);
}
