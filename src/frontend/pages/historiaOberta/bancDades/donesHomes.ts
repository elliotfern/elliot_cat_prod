import { api } from '../../../Infrastructure/Api/Client/ApiClient';
import { DOMAIN_WEB } from '../../../utils/urls';

interface Persona {
  id: string;
  nom: string;
  cognoms: string | null;
  slug: string;
  dia_naixement: number | null;
  mes_naixement: number | null;
  any_naixement: number | null;
  dia_defuncio: number | null;
  mes_defuncio: number | null;
  any_defuncio: number | null;
  pais: string | null;
  nameImg: string | null;
  grup: string[];
}

const GRUPS_PERSONES = ['0197b0881a27723c8ca798b4d3743bc4', '0197b0881a27723c8ca798b4d3876cfd'];

const PERSONES_PER_PAGINA = 20;

export async function donesHomes(): Promise<void> {
  const banc = document.getElementById('banc');

  if (!(banc instanceof HTMLDivElement)) {
    console.error('No s’ha trobat el contenidor #banc.');
    return;
  }

  banc.innerHTML = `
    <div class="text-center py-4">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Carregant...</span>
      </div>
      <p class="mt-2">Carregant persones...</p>
    </div>
  `;

  try {
    const grupId = GRUPS_PERSONES.join(',');

    const persones = await api.get<Persona[]>('persones/get/llistatPersonesPerGrup', { grup_id: grupId });

    if (!Array.isArray(persones) || persones.length === 0) {
      banc.innerHTML = `
        <h1 class="mb-4">Dones i homes que han fet història</h1>
        <div class="alert alert-info">
          No s’han trobat persones.
        </div>
      `;
      return;
    }

    const escaparHTML = (valor: string): string =>
      valor.replace(/[&<>"']/g, (caracter) => {
        const entitats: Record<string, string> = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;',
        };

        return entitats[caracter] ?? caracter;
      });

    const formatarData = (dia: number | null, mes: number | null, any: number | null): string => {
      if (any === null) return '';

      const parts: string[] = [];

      if (dia !== null) {
        parts.push(String(dia).padStart(2, '0'));
      }

      if (mes !== null) {
        parts.push(String(mes).padStart(2, '0'));
      }

      parts.push(String(any));

      return parts.join('/');
    };

    const crearTargetes = (llistat: Persona[]): string =>
      llistat
        .map((persona) => {
          const nomComplet = [persona.nom, persona.cognoms].filter((part): part is string => Boolean(part)).join(' ');

          const naixement = formatarData(persona.dia_naixement, persona.mes_naixement, persona.any_naixement);

          const defuncio = formatarData(persona.dia_defuncio, persona.mes_defuncio, persona.any_defuncio);

          const dates = [naixement, defuncio].filter(Boolean).join(' – ');

          const imatge = persona.nameImg
            ? `
              <a href="${DOMAIN_WEB}/historia/banc-dades/dones-homes/${encodeURIComponent(persona.slug)}">
                <img
                    src="https://media.elliot.cat/img/persona/${encodeURIComponent(persona.nameImg)}.jpg"
                    class="card-img-top"
                    alt="${escaparHTML(nomComplet)}"
                    loading="lazy"
                    style="height: 240px; object-fit: cover; object-position: top;"
                >
                </a>
            `
            : '';

          const pais = persona.pais ? `<span class="text-body-secondary">${escaparHTML(persona.pais)}</span>` : '';

          const grups = Array.isArray(persona.grup)
            ? persona.grup
                .map(
                  (grup) => `
                    <span class="badge text-bg-secondary me-1 mb-1">
                      ${escaparHTML(grup)}
                    </span>
                  `
                )
                .join('')
            : '';

          const url = `${DOMAIN_WEB}/historia/banc-dades/dones-homes/${encodeURIComponent(persona.slug)}`;

          return `
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
              <article class="card h-100 shadow-sm">
                ${imatge}

                <div class="card-body d-flex flex-column">
                  <h2 class="card-title h5">
                    ${escaparHTML(nomComplet)}
                  </h2>

                  ${dates ? `<p class="card-text small mb-2">${escaparHTML(dates)}</p>` : ''}

                  ${pais ? `<p class="card-text small mb-3">${pais}</p>` : ''}

                  ${grups ? `<div class="mb-3">${grups}</div>` : ''}

                  <a
                    href="${escaparHTML(url)}"
                    class="btn btn-outline-primary mt-auto"
                  >
                    Veure biografia
                  </a>
                </div>
              </article>
            </div>
          `;
        })
        .join('');

    const totalPagines = Math.ceil(persones.length / PERSONES_PER_PAGINA);

    const crearPaginacio = (paginaActual: number): string => {
      const anteriorDesactivat = paginaActual === 1;
      const seguentDesactivat = paginaActual === totalPagines;

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
        <li class="page-item ${anteriorDesactivat ? 'disabled' : ''}">
          <button
            type="button"
            class="page-link"
            data-pagina="${paginaActual - 1}"
            ${anteriorDesactivat ? 'disabled' : ''}
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
        const paginesVisibles = new Set<number>([1, totalPagines, paginaActual, paginaActual - 1, paginaActual + 1]);

        const ordenades = [...paginesVisibles].filter((pagina) => pagina >= 1 && pagina <= totalPagines).sort((a, b) => a - b);

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
        <li class="page-item ${seguentDesactivat ? 'disabled' : ''}">
          <button
            type="button"
            class="page-link"
            data-pagina="${paginaActual + 1}"
            ${seguentDesactivat ? 'disabled' : ''}
            aria-label="Pàgina següent"
          >
            <span class="d-none d-sm-inline">Següent</span>
            <span aria-hidden="true">&raquo;</span>
          </button>
        </li>
      `);

      return `
        <nav aria-label="Paginació de persones" class="mt-4">
          <ul class="pagination pagination-sm flex-wrap justify-content-center mb-2">
            ${elements.join('')}
          </ul>
        </nav>
      `;
    };

    banc.innerHTML = `
      <h1 class="mb-4">Dones i homes que han fet història</h1>

      <div id="persones-resultats" class="row g-3"></div>

      <div id="persones-resum" class="text-body-secondary small mt-3 text-center"></div>

      <div id="persones-paginacio"></div>
    `;

    const resultats = document.getElementById('persones-resultats');
    const resum = document.getElementById('persones-resum');
    const paginacio = document.getElementById('persones-paginacio');

    if (!(resultats instanceof HTMLDivElement) || !(resum instanceof HTMLDivElement) || !(paginacio instanceof HTMLDivElement)) {
      console.error('No s’han pogut inicialitzar els resultats de persones.');
      return;
    }

    const mostrarPagina = (pagina: number): void => {
      const paginaActual = Math.max(1, Math.min(pagina, totalPagines));

      const inici = (paginaActual - 1) * PERSONES_PER_PAGINA;
      const final = Math.min(inici + PERSONES_PER_PAGINA, persones.length);

      resultats.innerHTML = crearTargetes(persones.slice(inici, final));

      resum.textContent = `Mostrant ${inici + 1}–${final} de ${persones.length} persones`;

      paginacio.innerHTML = crearPaginacio(paginaActual);

      paginacio.querySelectorAll<HTMLButtonElement>('[data-pagina]').forEach((boto) => {
        boto.addEventListener('click', () => {
          const paginaSeleccionada = Number(boto.dataset.pagina);

          if (Number.isInteger(paginaSeleccionada) && paginaSeleccionada >= 1 && paginaSeleccionada <= totalPagines && paginaSeleccionada !== paginaActual) {
            mostrarPagina(paginaSeleccionada);
          }
        });
      });
    };

    mostrarPagina(1);
  } catch (error: unknown) {
    console.error('Error carregant les persones:', error);

    banc.innerHTML = `
      <div class="alert alert-danger" role="alert">
        No s’han pogut carregar les persones. Torna-ho a provar més tard.
      </div>
    `;
  }
}
