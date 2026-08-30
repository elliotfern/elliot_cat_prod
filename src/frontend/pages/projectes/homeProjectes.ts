import { api } from '../../core/api/client';
import { formatData } from '../../utils/formataData';

// ------------------------------------------------
// Tipos
// ------------------------------------------------

type TaskItem = {
  id: string;
  projecte_id: string | null;
  projecte: string | null;
  title: string;
  estat: 'pendent' | 'en_curs' | 'finalitzat' | 'arxivat';
  prioritat: 'baixa' | 'normal' | 'alta' | 'urgent';
  planned_date: string | null;
  is_next: number;
  blocked_reason: string | null;
  estimated_hours: string | number | null;
  updated_at: string | null;
};

type ProjectWithNext = {
  projecte_id: string;
  project_name: string;
  project_priority: 'baixa' | 'normal' | 'alta' | 'urgent';
  category_name: 'professional' | 'personal' | null;

  next_task_id: string | null;
  next_task_title: string | null;
  next_task_status: 'pendent' | 'en_curs' | 'finalitzat' | 'arxivat' | null;
  next_task_priority: 'baixa' | 'normal' | 'alta' | 'urgent' | null;
  blocked_reason: string | null;
};

type HomeData = {
  today: TaskItem[];
  blocked: TaskItem[];
  activeProjects: ProjectWithNext[];
};

// ------------------------------------------------
// URLs
// ------------------------------------------------

function projectEditUrl(projectId: string): string {
  return `/gestio/projectes/modifica-projecte/${encodeURIComponent(projectId)}`;
}

function projectDetailsUrl(projectId: string): string {
  return `/gestio/projectes/fitxa-projecte/${encodeURIComponent(projectId)}`;
}

// ------------------------------------------------
// DOM
// ------------------------------------------------

function el<T extends Element>(id: string): T | null {
  return document.getElementById(id) as T | null;
}

// ------------------------------------------------
// Escape HTML
// ------------------------------------------------

function esc(s: string): string {
  return s.replace(/[&<>"']/g, (c) => {
    switch (c) {
      case '&':
        return '&amp;';
      case '<':
        return '&lt;';
      case '>':
        return '&gt;';
      case '"':
        return '&quot;';
      case "'":
        return '&#039;';
      default:
        return c;
    }
  });
}

// ------------------------------------------------
// Badges
// ------------------------------------------------

function badgeEstat(estat: TaskItem['estat'] | ProjectWithNext['next_task_status']): string {
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
      return '';
  }
}

function badgePrioritat(prioritat: TaskItem['prioritat'] | ProjectWithNext['next_task_priority']): string {
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
      return '';
  }
}

function badgeCategoria(categoria: ProjectWithNext['category_name']): string {
  switch (categoria) {
    case 'professional':
      return '<span class="badge bg-primary">Professional</span>';

    case 'personal':
      return '<span class="badge bg-success">Personal</span>';

    default:
      return '';
  }
}

function badge(text: string, cls = 'bg-secondary'): string {
  return `<span class="badge ${cls}">${esc(text)}</span>`;
}

// ------------------------------------------------
// Dates
// ------------------------------------------------

function formatDate(value: string | null): string {
  if (!value || !value.trim()) {
    return '—';
  }

  return formatData(value);
}

// ------------------------------------------------
// Project priority
// ------------------------------------------------

function projectPriorityBtn(prioritat: ProjectWithNext['project_priority']): string {
  switch (prioritat) {
    case 'baixa':
      return `
        <span class="btn btn-sm btn-success disabled">
          Baixa
        </span>
      `;

    case 'normal':
      return `
        <span class="btn btn-sm btn-primary disabled">
          Normal
        </span>
      `;

    case 'alta':
      return `
        <span class="btn btn-sm btn-warning disabled">
          Alta
        </span>
      `;

    case 'urgent':
      return `
        <span class="btn btn-sm btn-danger disabled">
          Urgent
        </span>
      `;

    default:
      return '';
  }
}

// ------------------------------------------------
// Avui
// ------------------------------------------------

function renderTodayCard(items: TaskItem[]): string {
  const rows = items
    .map(
      (t) => `
        <tr>

          <td>
            ${
              t.projecte
                ? `
                  <a
                    class="text-decoration-none"
                    href="${projectDetailsUrl(t.projecte_id ?? '')}"
                  >
                    ${esc(t.projecte)}
                  </a>
                `
                : '—'
            }
          </td>

          <td>

            <div class="fw-semibold">
              ${esc(t.title)}
            </div>

            ${
              t.blocked_reason
                ? `
                  <div class="small text-muted mt-1">
                    ${badge('Bloqueig', 'bg-warning text-dark')}
                    ${esc(t.blocked_reason)}
                  </div>
                `
                : ''
            }

          </td>

          <td class="text-nowrap">

            ${badgePrioritat(t.prioritat)}

            ${badgeEstat(t.estat)}

            ${Number(t.is_next) === 1 ? badge('NEXT', 'bg-primary') : ''}

          </td>

          <td class="text-nowrap">
            ${formatDate(t.planned_date)}
          </td>

        </tr>
      `
    )
    .join('');

  const body = items.length
    ? `
      <div class="table-responsive">

        <table class="table table-sm align-middle mb-0">

          <thead>
            <tr>
              <th>Projecte</th>
              <th>Tasca</th>
              <th>Info</th>
              <th>Data</th>
            </tr>
          </thead>

          <tbody>
            ${rows}
          </tbody>

        </table>

      </div>
    `
    : `
      <div class="text-muted">
        No tens tasques planificades per avui.
      </div>
    `;

  return `
    <div class="card h-100">

      <div class="card-body">

        <h5 class="card-title mb-0">
          Avui
        </h5>

        <div class="mt-3">
          ${body}
        </div>

      </div>

    </div>
  `;
}

// ------------------------------------------------
// Tasques en curs
// ------------------------------------------------

function renderBlockedCard(items: TaskItem[]): string {
  const rows = items
    .map(
      (t) => `
        <tr>

          <td>
            ${
              t.projecte
                ? `
                  <a
                    class="text-decoration-none"
                    href="${projectDetailsUrl(t.projecte_id ?? '')}"
                  >
                    ${esc(t.projecte)}
                  </a>
                `
                : '—'
            }
          </td>

          <td>

            <div class="fw-semibold">
              ${esc(t.title)}
            </div>

            ${
              t.blocked_reason
                ? `
                  <div class="small text-muted mt-1">
                    ${esc(t.blocked_reason)}
                  </div>
                `
                : ''
            }

          </td>

          <td class="text-nowrap">

            ${badgePrioritat(t.prioritat)}

            ${badgeEstat(t.estat)}

          </td>

          <td class="text-nowrap">
            ${formatDate(t.planned_date)}
          </td>

        </tr>
      `
    )
    .join('');

  const body = items.length
    ? `
      <div class="table-responsive">

        <table class="table table-sm align-middle mb-0">

          <thead>
            <tr>
              <th>Projecte</th>
              <th>Tasca</th>
              <th>Info</th>
              <th>Data</th>
            </tr>
          </thead>

          <tbody>
            ${rows}
          </tbody>

        </table>

      </div>
    `
    : `
      <div class="text-muted">
        No hi ha tasques en curs.
      </div>
    `;

  return `
    <div class="card h-100">

      <div class="card-body">

        <h5 class="card-title mb-0">
          Tasques en curs
        </h5>

        <div class="mt-3">
          ${body}
        </div>

      </div>

    </div>
  `;
}

// ------------------------------------------------
// Projectes actius
// ------------------------------------------------

function renderActiveProjectsCard(items: ProjectWithNext[]): string {
  const rows = items
    .map((p) => {
      const nextHtml = p.next_task_title
        ? `
          <div class="fw-semibold">
            ${esc(p.next_task_title)}
          </div>

          <div class="small mt-1">

            ${p.next_task_status ? badgeEstat(p.next_task_status) : ''}

            ${p.next_task_priority ? badgePrioritat(p.next_task_priority) : ''}

            ${
              p.blocked_reason
                ? `
                  ${badge('Bloqueig', 'bg-warning text-dark')}
                  ${esc(p.blocked_reason)}
                `
                : ''
            }

          </div>
        `
        : `
          <div class="text-muted">
            Sense NEXT definit
          </div>
        `;

      return `
        <tr>

          <!-- Projecte -->
          <td>

            <a
              class="text-decoration-none link-dark"
              href="${projectDetailsUrl(p.projecte_id)}"
            >

              <div class="fw-semibold">
                ${esc(p.project_name)}
              </div>

            </a>

            <div class="small mt-1">
              ${badgeCategoria(p.category_name)}
            </div>

          </td>

          <!-- NEXT -->
          <td>
            ${nextHtml}
          </td>

          <!-- Prioritat -->
          <td class="text-nowrap">
            ${projectPriorityBtn(p.project_priority)}
          </td>

          <!-- Accions -->
          <td class="text-nowrap text-end">

            <a
              class="btn btn-sm btn-outline-primary"
              href="${projectEditUrl(p.projecte_id)}"
            >
              Modificar
            </a>

          </td>

        </tr>
      `;
    })
    .join('');

  const body = items.length
    ? `
      <div class="table-responsive">

        <table class="table table-sm align-middle mb-0">

          <thead>

            <tr>
              <th>Projecte</th>
              <th>Next</th>
              <th>Prioritat</th>
              <th class="text-end">Accions</th>
            </tr>

          </thead>

          <tbody>
            ${rows}
          </tbody>

        </table>

      </div>
    `
    : `
      <div class="text-muted">
        No hi ha projectes actius.
      </div>
    `;

  return `
    <div class="card">

      <div class="card-body">

        <h5 class="card-title mb-0">
          Projectes actius
        </h5>

        <div class="mt-3">
          ${body}
        </div>

      </div>

    </div>
  `;
}

// ------------------------------------------------
// Init
// ------------------------------------------------

export async function initProjectesHome(): Promise<void> {
  const panels = el<HTMLDivElement>('projectesHomePanels');
  const actius = el<HTMLDivElement>('panelProjectesActius');

  if (!panels || !actius) {
    return;
  }

  panels.innerHTML = `
    <div class="text-muted">
      Carregant...
    </div>
  `;

  actius.innerHTML = '';

  try {
    const data = await api.get<HomeData>('projectes/get/home');

    panels.innerHTML = `
      <div class="row g-3">

        <div class="col-12 col-lg-6">
          ${renderTodayCard(data.today ?? [])}
        </div>

        <div class="col-12 col-lg-6">
          ${renderBlockedCard(data.blocked ?? [])}
        </div>

      </div>
    `;

    actius.innerHTML = renderActiveProjectsCard(data.activeProjects ?? []);
  } catch (error) {
    console.error(error);

    panels.innerHTML = `
      <div class="alert alert-danger mb-0">
        No s'han pogut carregar els panells de la Home.
      </div>
    `;
  }
}
