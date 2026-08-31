import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { ProjecteDetalls } from '../../types/Projecte';
import { Tasca } from '../../types/Tasca';
import { API_URLS } from '../../utils/apiUrls';
import { formatData } from '../../utils/formataData';

// ------------------------------------------------
// Helpers UI
// ------------------------------------------------

function setText(id: string, value: string): void {
  const el = document.getElementById(id);

  if (!el) return;

  el.textContent = value;
}

function escapeHtml(value: string): string {
  return value.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
}

// ------------------------------------------------
// Badges
// ------------------------------------------------

function badgeEstat(estat: string): string {
  switch (estat) {
    case 'pendent':
      return '<span class="badge bg-secondary">Pendent</span>';

    case 'en_curs':
      return '<span class="badge bg-primary">En curs</span>';

    case 'finalitzat':
      return '<span class="badge bg-success">Finalitzat</span>';

    case 'arxivat':
      return '<span class="badge bg-dark">Arxivat</span>';

    default:
      return escapeHtml(estat);
  }
}

function badgePrioritat(prioritat: string): string {
  switch (prioritat) {
    case 'baixa':
      return '<span class="badge bg-secondary">Baixa</span>';

    case 'normal':
      return '<span class="badge bg-primary">Normal</span>';

    case 'alta':
      return '<span class="badge bg-warning text-dark">Alta</span>';

    case 'urgent':
      return '<span class="badge bg-danger">Urgent</span>';

    default:
      return escapeHtml(prioritat);
  }
}

function badgeCategoria(categoria: string): string {
  switch (categoria) {
    case 'professional':
      return '<span class="badge bg-primary">Professional</span>';

    case 'personal':
      return '<span class="badge bg-success">Personal</span>';

    default:
      return escapeHtml(categoria);
  }
}

// ------------------------------------------------
// Projecte
// ------------------------------------------------

export async function initProjecteDetalls(id: string): Promise<void> {
  // ------------------------------------------------
  // Validar ID
  // ------------------------------------------------

  if (!id || !id.trim()) {
    console.error('ID de projecte invàlid');
    return;
  }

  // ------------------------------------------------
  // Containers
  // ------------------------------------------------

  const header = document.getElementById('projecteDetallsHeader');
  const fitxa = document.getElementById('projecteDetallsFitxa');
  const kpisBox = document.getElementById('projecteDetallsKpis');
  const tasquesBox = document.getElementById('projecteDetallsTasques');

  if (!header || !fitxa || !kpisBox || !tasquesBox) {
    console.error("No s'han trobat els contenidors del detall del projecte");

    return;
  }

  // ------------------------------------------------
  // 1) GET projecte
  // ------------------------------------------------

  let projecte: ProjecteDetalls;

  try {
    projecte = await api.get<ProjecteDetalls>(API_URLS.GET.PROJECTE_DETALLS, {
      id,
    });
  } catch (error) {
    console.error(error);

    header.innerHTML = '';

    fitxa.innerHTML = `
      <div class="alert alert-danger">
        No s'han pogut carregar els detalls del projecte.
      </div>
    `;

    kpisBox.innerHTML = '';
    tasquesBox.innerHTML = '';

    return;
  }

  // ------------------------------------------------
  // Header
  // ------------------------------------------------

  header.innerHTML = `
    <div>
      <h3 class="mb-1">
        ${escapeHtml(projecte.projecte ?? '—')}
      </h3>

      <div class="text-muted small">
        #${escapeHtml(projecte.id)}
      </div>
    </div>
  `;

  // ------------------------------------------------
  // Fitxa
  // ------------------------------------------------

  fitxa.innerHTML = `
    <div class="card">

      <div class="card-body">

        <div class="d-flex justify-content-end mb-4">

          <a
            href="/gestio/projectes/modifica-projecte/${encodeURIComponent(projecte.id)}"
            class="btn btn-warning"
          >
            Modifica projecte
          </a>

        </div>

        <div class="row g-4">

          <!-- Estat -->
          <div class="col-12 col-md-6">

            <div class="small text-muted mb-1">
              Estat
            </div>

            <div>
              ${badgeEstat(projecte.estat)}
            </div>

          </div>

          <!-- Prioritat -->
          <div class="col-12 col-md-6">

            <div class="small text-muted mb-1">
              Prioritat
            </div>

            <div>
              ${badgePrioritat(projecte.prioritat)}
            </div>

          </div>

          <!-- Categoria -->
          <div class="col-12 col-md-6">

            <div class="small text-muted mb-1">
              Categoria
            </div>

            <div>
              ${badgeCategoria(projecte.categoria)}
            </div>

          </div>

          <!-- Client -->
          <div class="col-12 col-md-6">

            <div class="small text-muted mb-1">
              Client
            </div>

            <div>
              ${projecte.nom ? escapeHtml(`${projecte.nom} ${projecte.cognoms ?? ''}`.trim()) + (projecte.empresa ? ` (${escapeHtml(projecte.empresa)})` : '') : '—'}
            </div>

          </div>

          <!-- Data inici -->
          <div class="col-12 col-md-6">

            <div class="small text-muted mb-1">
              Data inici
            </div>

            <div>
              ${projecte.data_inici ? escapeHtml(formatData(projecte.data_inici)) : '—'}
            </div>

          </div>

          <!-- Data fi -->
          <div class="col-12 col-md-6">

            <div class="small text-muted mb-1">
              Data fi
            </div>

            <div>
              ${projecte.data_fi ? escapeHtml(formatData(projecte.data_fi)) : '—'}
            </div>

          </div>

          <!-- Descripció -->
          <div class="col-12">

            <div class="small text-muted mb-1">
              Descripció
            </div>

            <div>
              ${projecte.descripcio ? escapeHtml(projecte.descripcio) : '—'}
            </div>

          </div>

        </div>

      </div>

    </div>
  `;

  // ------------------------------------------------
  // Subtítol
  // ------------------------------------------------

  setText('subtitolProjecte', `Detalls del projecte · #${projecte.id}`);

  // ------------------------------------------------
  // 2) GET tasques del projecte
  // ------------------------------------------------

  let tasques: Tasca[];

  try {
    tasques = await api.get<Tasca[]>(API_URLS.GET.PROJECTE_TASQUES, {
      id,
    });
  } catch (error) {
    console.error(error);

    kpisBox.innerHTML = '';

    tasquesBox.innerHTML = `
      <div class="alert alert-danger">
        No s'han pogut carregar les tasques del projecte.
      </div>
    `;

    return;
  }

  // ------------------------------------------------
  // KPIs simples
  // ------------------------------------------------

  const total = tasques.length;

  const finalitzades = tasques.filter((t) => t.estat === 'finalitzat').length;

  const enCurs = tasques.filter((t) => t.estat === 'en_curs').length;

  const pendents = tasques.filter((t) => t.estat === 'pendent').length;

  const next = tasques.filter((t) => Number(t.is_next) === 1).length;

  kpisBox.innerHTML = `
    <div class="d-flex flex-wrap gap-3">

      <div class="border rounded px-3 py-2">
        <strong>${total}</strong>
        <span class="text-muted">tasques</span>
      </div>

      <div class="border rounded px-3 py-2">
        <strong>${finalitzades}</strong>
        <span class="text-muted">finalitzades</span>
      </div>

      <div class="border rounded px-3 py-2">
        <strong>${enCurs}</strong>
        <span class="text-muted">en curs</span>
      </div>

      <div class="border rounded px-3 py-2">
        <strong>${pendents}</strong>
        <span class="text-muted">pendents</span>
      </div>

      <div class="border rounded px-3 py-2">
        <strong>${next}</strong>
        <span class="text-muted">next</span>
      </div>

    </div>
  `;

  // ------------------------------------------------
  // 3) Tasques
  // ------------------------------------------------

  tasquesBox.innerHTML = `
    <div class="table-responsive">

      <table class="table table-striped align-middle">

        <thead class="table-primary">

          <tr>
            <th>Títol</th>
            <th>Estat</th>
            <th>Prioritat</th>
            <th>Data</th>
            <th>Next</th>
            <th></th>
          </tr>

        </thead>

        <tbody>

          ${
            tasques.length > 0
              ? tasques
                  .map(
                    (t) => `
                      <tr>

                        <td>
                          ${escapeHtml(t.title ?? '')}
                        </td>

                        <td>
                          ${badgeEstat(t.estat)}
                        </td>

                        <td>
                          ${badgePrioritat(t.prioritat)}
                        </td>

                        <td>
                          ${t.planned_date ? escapeHtml(formatData(t.planned_date)) : '—'}
                        </td>

                        <td>
                          ${Number(t.is_next) === 1 ? '✅' : ''}
                        </td>

                        <td class="text-end">

                          <a
                            class="btn btn-sm btn-outline-secondary"
                            href="/gestio/projectes/modifica-tasca/${encodeURIComponent(t.id)}"
                          >
                            Edita
                          </a>

                        </td>

                      </tr>
                    `
                  )
                  .join('')
              : `
                <tr>

                  <td
                    colspan="6"
                    class="text-muted"
                  >
                    No hi ha tasques.
                  </td>

                </tr>
              `
          }

        </tbody>

      </table>

    </div>
  `;
}
