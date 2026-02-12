type ApiEnvelope<T> = {
  status: string;
  message: string;
  errors: unknown[];
  data: T;
};

type TaskItem = {
  id: number;
  project_id: number | null;
  project_name: string | null;
  title: string;
  status: number;
  priority: number;
  planned_date: string | null;
  is_next: number;
  blocked_reason: string | null;
  estimated_hours: string | number | null;
  updated_at: string | null;
};

type ProjectWithNext = {
  project_id: number;
  project_name: string;
  project_priority: number;
  category_name: string | null;

  next_task_id: number | null;
  next_task_title: string | null;
  next_task_status: number | null;
  next_task_priority: number | null;
  blocked_reason: string | null;
};

type HomeData = {
  today: TaskItem[];
  blocked: TaskItem[];
  activeProjects: ProjectWithNext[];
};

function projectEditUrl(projectId: number): string {
  return `/gestio/projectes/modifica-projecte/${encodeURIComponent(String(projectId))}`;
}

function el<T extends Element>(id: string): T | null {
  return document.getElementById(id) as T | null;
}

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

function taskStatusLabel(n: number): string {
  switch (n) {
    case 1:
      return 'Backlog';
    case 2:
      return 'En curs';
    case 3:
      return 'Bloquejada';
    case 4:
      return 'Feta';
    default:
      return '—';
  }
}

function prioLabel(n: number): string {
  // Ajusta si usas 1..3 o 1..5
  switch (n) {
    case 1:
      return 'Alta';
    case 2:
      return 'Mitja';
    case 3:
      return 'Normal';
    case 4:
      return 'Baixa';
    case 5:
      return 'Molt baixa';
    default:
      return String(n);
  }
}

function badge(text: string, cls = 'text-bg-secondary'): string {
  return `<span class="badge ${cls}">${esc(text)}</span>`;
}

function renderTodayCard(items: TaskItem[]): string {
  const rows = items
    .map(
      (t) => `
    <tr>
      <td>${esc(t.project_name ?? '—')}</td>
      <td>
        <div class="fw-semibold">${esc(t.title)}</div>
        ${t.status === 3 && t.blocked_reason ? `<div class="small text-muted mt-1">${badge('Bloqueig', 'text-bg-warning')} ${esc(t.blocked_reason)}</div>` : ''}
      </td>
      <td class="text-nowrap">
        ${badge(prioLabel(t.priority), 'text-bg-light text-dark')}
        ${badge(taskStatusLabel(t.status), t.status === 3 ? 'text-bg-warning' : 'text-bg-secondary')}
        ${t.is_next ? badge('NEXT', 'text-bg-primary') : ''}
      </td>
    </tr>
  `
    )
    .join('');

  const body = items.length
    ? `<div class="table-responsive"><table class="table table-sm align-middle mb-0">
         <thead><tr><th>Projecte</th><th>Tasca</th><th>Info</th></tr></thead>
         <tbody>${rows}</tbody>
       </table></div>`
    : `<div class="text-muted">No tens tasques planificades per avui.</div>`;

  return `
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">Avui</h5>
        </div>
        <div class="mt-3">${body}</div>
      </div>
    </div>
  `;
}

function renderBlockedCard(items: TaskItem[]): string {
  const rows = items
    .map(
      (t) => `
    <tr>
      <td>${esc(t.project_name ?? '—')}</td>
      <td>
        <div class="fw-semibold">${esc(t.title)}</div>
        <div class="small text-muted mt-1">${esc(t.blocked_reason ?? '—')}</div>
      </td>
      <td class="text-nowrap">
        ${badge(prioLabel(t.priority), 'text-bg-light text-dark')}
        ${badge('Bloquejada', 'text-bg-warning')}
      </td>
    </tr>
  `
    )
    .join('');

  const body = items.length
    ? `<div class="table-responsive"><table class="table table-sm align-middle mb-0">
         <thead><tr><th>Projecte</th><th>Tasca</th><th>Info</th></tr></thead>
         <tbody>${rows}</tbody>
       </table></div>`
    : `<div class="text-muted">No tens tasques bloquejades.</div>`;

  return `
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-0">Bloquejades</h5>
        <div class="mt-3">${body}</div>
      </div>
    </div>
  `;
}

function renderActiveProjectsCard(items: ProjectWithNext[]): string {
  const rows = items
    .map((p) => {
      const nextHtml = p.next_task_title
        ? `<div class="fw-semibold">${esc(p.next_task_title)}</div>
         <div class="small text-muted mt-1">
           ${badge(taskStatusLabel(p.next_task_status ?? 1), 'text-bg-secondary')}
           ${badge('Prio ' + prioLabel(p.next_task_priority ?? 3), 'text-bg-light text-dark')}
           ${p.blocked_reason ? ' ' + badge('Bloqueig', 'text-bg-warning') + ' ' + esc(p.blocked_reason) : ''}
         </div>`
        : `<div class="text-muted">Sense NEXT definit</div>`;

      const editHref = projectEditUrl(p.project_id);

      return `
      <tr>
        <td>
          <div class="fw-semibold">${esc(p.project_name)}</div>
          ${p.category_name ? `<div class="small text-muted">${esc(p.category_name)}</div>` : ''}
        </td>
        <td>${nextHtml}</td>
        <td class="text-nowrap">${badge('Prio ' + prioLabel(p.project_priority), 'text-bg-light text-dark')}</td>
        <td class="text-nowrap text-end">
          <a class="btn btn-sm btn-outline-primary" href="${editHref}">
            Modificar
          </a>
        </td>
      </tr>
    `;
    })
    .join('');

  const body = items.length
    ? `<div class="table-responsive"><table class="table table-sm align-middle mb-0">
         <thead>
           <tr>
             <th>Projecte</th>
             <th>Next</th>
             <th>Info</th>
             <th class="text-end">Accions</th>
           </tr>
         </thead>
         <tbody>${rows}</tbody>
       </table></div>`
    : `<div class="text-muted">No hi ha projectes actius.</div>`;

  return `
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-0">Projectes actius</h5>
        <div class="mt-3">${body}</div>
      </div>
    </div>
  `;
}

async function fetchHome(): Promise<HomeData> {
  const res = await fetch('/api/projectes/get/home', {
    method: 'GET',
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });

  if (!res.ok) {
    const t = await res.text().catch(() => '');
    throw new Error(`HTTP ${res.status}: ${t}`);
  }

  const json = (await res.json()) as ApiEnvelope<HomeData>;
  if (!json || json.status !== 'success') {
    throw new Error(json?.message ?? 'API error');
  }

  return json.data;
}

export async function initProjectesHome(): Promise<void> {
  const panels = el<HTMLDivElement>('projectesHomePanels');
  const actius = el<HTMLDivElement>('panelProjectesActius');
  if (!panels || !actius) return;

  panels.innerHTML = `<div class="text-muted">Carregant...</div>`;
  actius.innerHTML = '';

  try {
    const data = await fetchHome();

    panels.innerHTML = `
      <div class="row g-3">
        <div class="col-12 col-lg-6">${renderTodayCard(data.today ?? [])}</div>
        <div class="col-12 col-lg-6">${renderBlockedCard(data.blocked ?? [])}</div>
      </div>
    `;

    actius.innerHTML = renderActiveProjectsCard(data.activeProjects ?? []);
  } catch (e) {
    console.error(e);
    panels.innerHTML = `
      <div class="alert alert-danger mb-0">
        No s'han pogut carregar els panells de la Home.
      </div>
    `;
  }
}
