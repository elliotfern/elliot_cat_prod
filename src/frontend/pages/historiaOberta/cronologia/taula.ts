import { state } from '../../../types/Esdeveniment';

let esAdminActual = false;

const mesos = ['', 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'];

export function formatDataCatalana(dia?: number, mes?: number, any?: number): string {
  if (!any) return '';

  const d = dia && dia !== 0 ? dia : '';
  const m = mes && mes !== 0 ? mesos[mes] : '';

  if (d && m) return `${d} ${m} ${any}`;
  if (m) return `${m} ${any}`;

  return `${any}`;
}

export function renderTabla(container: HTMLElement, esAdmin: boolean): void {
  esAdminActual = esAdmin;

  const row = document.createElement('div');
  row.id = 'esdeveniments-cards';
  row.className = 'row g-3';

  container.appendChild(row);

  pintarTabla();
}

export function pintarTabla(): void {
  const container = document.getElementById('esdeveniments-cards');
  if (!container) return;

  const inici = (state.paginaActual - 1) * state.eventosPorPagina;
  const esdeveniments = state.eventos.slice(inici, inici + state.eventosPorPagina);

  container.innerHTML = esdeveniments
    .map((e) => {
      const titol = escaparHTML(e.esdeNom);
      const slug = encodeURIComponent(e.slug);
      const subetapa = e.nomSubEtapa ? escaparHTML(e.nomSubEtapa) : '';
      const data = formatDataCatalana(e.esdeDataIDia, e.esdeDataIMes, e.esdeDataIAny);
      const imatge = e.nameImg ? `https://media.elliot.cat/img/historia-esdeveniment/${encodeURIComponent(e.nameImg)}.jpg` : '';
      const alt = escaparHTML(e.alt ?? e.esdeNom);
      const urlFitxa = `/gestio/historia/fitxa-esdeveniment/${slug}`;

      return `
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
          <article class="card h-100 shadow-sm">
            ${
              imatge
                ? `
                  <a href="${urlFitxa}">
                    <img
                      src="${imatge}"
                      class="card-img-top"
                      alt="${alt}"
                      loading="lazy"
                      style="height: 240px; object-fit: cover; object-position: top;"
                    >
                  </a>
                `
                : ''
            }

            <div class="card-body d-flex flex-column">
              <h2 class="card-title h5">
                ${titol}
              </h2>

              ${data ? `<p class="card-text small mb-2">${escaparHTML(data)}</p>` : ''}

              ${
                subetapa
                  ? `
                    <div class="mb-3">
                      <span class="badge text-bg-secondary me-1 mb-1">
                        ${subetapa}
                      </span>
                    </div>
                  `
                  : ''
              }

              <a
                href="${urlFitxa}"
                class="btn btn-outline-primary mt-auto"
              >
                Veure esdeveniment
              </a>

              ${
                esAdminActual
                  ? `
                    <a
                      href="/gestio/historia/modifica-esdeveniment/${encodeURIComponent(e.id)}"
                      class="btn btn-warning mt-2"
                    >
                      Modifica
                    </a>
                  `
                  : ''
              }
            </div>
          </article>
        </div>
      `;
    })
    .join('');
}

function escaparHTML(valor: string): string {
  return valor.replace(/[&<>"']/g, (caracter) => {
    const entitats: Record<string, string> = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;',
    };

    return entitats[caracter] ?? caracter;
  });
}
