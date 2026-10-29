import { state } from '../../../types/Esdeveniment';
import { pintarTabla } from './taula';

export function renderPaginacion(container: HTMLElement): void {
  let resum = container.querySelector<HTMLDivElement>('#esdeveniments-resum');
  let paginacio = container.querySelector<HTMLDivElement>('#esdeveniments-paginacio');

  if (!resum) {
    resum = document.createElement('div');
    resum.id = 'esdeveniments-resum';
    resum.className = 'text-body-secondary small mt-3 text-center';
    container.appendChild(resum);
  }

  if (!paginacio) {
    paginacio = document.createElement('div');
    paginacio.id = 'esdeveniments-paginacio';
    container.appendChild(paginacio);
  }

  const totalEsdeveniments = state.eventos.length;
  const totalPagines = Math.ceil(totalEsdeveniments / state.eventosPorPagina);

  const mostrarPagina = (pagina: number): void => {
    const paginaActual = Math.max(1, Math.min(pagina, totalPagines));

    state.paginaActual = paginaActual;

    const inici = (paginaActual - 1) * state.eventosPorPagina;
    const final = Math.min(inici + state.eventosPorPagina, totalEsdeveniments);

    pintarTabla();

    resum!.textContent = totalEsdeveniments > 0 ? `Mostrant ${inici + 1}–${final} de ${totalEsdeveniments} esdeveniments` : 'No hi ha esdeveniments';

    paginacio!.innerHTML = crearPaginacio(paginaActual, totalPagines);

    paginacio!.querySelectorAll<HTMLButtonElement>('[data-pagina]').forEach((boto) => {
      boto.addEventListener('click', () => {
        const paginaSeleccionada = Number(boto.dataset.pagina);

        if (Number.isInteger(paginaSeleccionada) && paginaSeleccionada >= 1 && paginaSeleccionada <= totalPagines && paginaSeleccionada !== paginaActual) {
          mostrarPagina(paginaSeleccionada);

          window.scrollTo({
            top: container.offsetTop,
            behavior: 'smooth',
          });
        }
      });
    });
  };

  mostrarPagina(state.paginaActual);
}

function crearPaginacio(paginaActual: number, totalPagines: number): string {
  if (totalPagines <= 1) return '';

  const elements: string[] = [];

  const afegirPagina = (pagina: number): void => {
    elements.push(`
      <li class="page-item ${pagina === paginaActual ? 'active' : ''}">
        <button
          type="button"
          class="page-link"
          data-pagina="${pagina}"
          ${pagina === paginaActual ? 'aria-current="page"' : ''}
          aria-label="Pàgina ${pagina}"
        >
          ${pagina}
        </button>
      </li>
    `);
  };

  const afegirSeparador = (): void => {
    elements.push(`
      <li class="page-item disabled d-none d-sm-block" aria-hidden="true">
        <span class="page-link">…</span>
      </li>
    `);
  };

  elements.push(`
    <li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
      <button
        type="button"
        class="page-link"
        data-pagina="${paginaActual - 1}"
        ${paginaActual === 1 ? 'disabled' : ''}
        aria-label="Pàgina anterior"
      >
        <span aria-hidden="true">&laquo;</span>
        <span class="d-none d-sm-inline">Anterior</span>
      </button>
    </li>
  `);

  if (totalPagines <= 5) {
    for (let pagina = 1; pagina <= totalPagines; pagina++) {
      afegirPagina(pagina);
    }
  } else {
    const visibles = new Set<number>([1, totalPagines, paginaActual - 1, paginaActual, paginaActual + 1]);

    const ordenades = [...visibles].filter((pagina) => pagina >= 1 && pagina <= totalPagines).sort((a, b) => a - b);

    let paginaAnterior = 0;

    for (const pagina of ordenades) {
      if (paginaAnterior > 0 && pagina - paginaAnterior > 1) {
        afegirSeparador();
      }

      afegirPagina(pagina);
      paginaAnterior = pagina;
    }
  }

  elements.push(`
    <li class="page-item ${paginaActual === totalPagines ? 'disabled' : ''}">
      <button
        type="button"
        class="page-link"
        data-pagina="${paginaActual + 1}"
        ${paginaActual === totalPagines ? 'disabled' : ''}
        aria-label="Pàgina següent"
      >
        <span class="d-none d-sm-inline">Següent</span>
        <span aria-hidden="true">&raquo;</span>
      </button>
    </li>
  `);

  return `
    <nav aria-label="Paginació d'esdeveniments" class="mt-4">
      <ul class="pagination pagination-sm flex-wrap justify-content-center mb-2">
        ${elements.join('')}
      </ul>
    </nav>
  `;
}
