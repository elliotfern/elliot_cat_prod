// src/pages/projectes/home.ts
type TaskItem = {
  id: number;
  project_id: number | null;
  project_name?: string | null;
  title: string;
  status: number; // 1 backlog, 2 en_curso, 3 bloqueada, 4 hecha
  priority: number; // 1..5
  planned_date?: string | null;
  is_next?: number | boolean;
  blocked_reason?: string | null;
  estimated_hours?: string | number | null;
  updated_at?: string | null;
};

type ProjectWithNext = {
  project_id: number;
  project_name: string;
  project_priority: number;
  category_name?: string | null;

  next_task_id?: number | null;
  next_task_title?: string | null;
  next_task_status?: number | null;
  next_task_priority?: number | null;
  blocked_reason?: string | null;
};

type HomeApi = {
  today: TaskItem[];
  blocked: TaskItem[];
  activeProjects: ProjectWithNext[];
  wip?: { project_id: number; in_progress: number }[];
};

function qs<T extends Element>(sel: string): T | null {
  return document.querySelector(sel) as T | null;
}

function escapeHtml(s: string): string {
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

function statusLabel(status: number): string {
  switch (status) {
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

function priorityLabel(p: number): string {
  // Ajusta a tu gusto (1 alta ... 5 baja)
  switch (p) {
    case 1:
      return 'Alta';
    case 2:
      return 'Mitja-alta';
    case 3:
      return 'Mitja';
    case 4:
      return 'Baixa';
    case 5:
      return 'Molt baixa';
    default:
      return String(p);
  }
}

function badge(text: string, extraClass = ''): string {
  // Usa tus clases si ya tienes (btn/label/badge). Esto es neutro.
  return `<span class="badge ${extraClass}" style="display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid rgba(0,0,0,.15);font-size:12px;">${escapeHtml(text)}</span>`;
}

function card(title: string, bodyHtml: string, footerHtml = ''): string {
  return `
  <section class="card" style="border:1px solid rgba(0,0,0,.12);border-radius:12px;padding:14px;">
    <header style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
      <h2 style="margin:0;font-size:18px;">${escapeHtml(title)}</h2>
    </header>
    <div style="margin-top:10px;">
      ${bodyHtml}
    </div>
    ${footerHtml ? `<footer style="margin-top:10px;">${footerHtml}</footer>` : ''}
  </section>`;
}

function table(headers: string[], rows: string[][]): string {
  const thead = `<thead><tr>${headers.map((h) => `<th style="text-align:left;padding:8px;border-bottom:1px solid rgba(0,0,0,.12);">${escapeHtml(h)}</th>`).join('')}</tr></thead>`;
  const tbody = rows.length ? `<tbody>${rows.map((r) => `<tr>${r.map((c) => `<td style="padding:8px;border-bottom:1px solid rgba(0,0,0,.06);vertical-align:top;">${c}</td>`).join('')}</tr>`).join('')}</tbody>` : `<tbody><tr><td colspan="${headers.length}" style="padding:10px;opacity:.7;">No hi ha elements.</td></tr></tbody>`;
  return `<div style="overflow:auto;"><table style="width:100%;border-collapse:collapse;">${thead}${tbody}</table></div>`;
}

function renderPanelToday(items: TaskItem[]): string {
  const rows = items.map((t) => {
    const project = t.project_name ? escapeHtml(t.project_name) : '—';
    const title = escapeHtml(t.title);
    const st = badge(statusLabel(t.status));
    const pr = badge(priorityLabel(t.priority));
    const next = t.is_next ? badge('NEXT', 'badge-next') : '';
    const blocked = t.status === 3 && t.blocked_reason ? `<div style="margin-top:4px;opacity:.8;">${badge('Bloqueig')} ${escapeHtml(t.blocked_reason)}</div>` : '';
    return [project, `<div><div style="font-weight:600;">${title}</div>${blocked}</div>`, `${pr} ${st} ${next}`.trim()];
  });

  const body = table(['Projecte', 'Tasca', 'Info'], rows);
  return card('Avui', body, `<div style="opacity:.8;font-size:13px;">Tasca planificada = <code>planned_date</code> = avui</div>`);
}

function renderPanelBlocked(items: TaskItem[]): string {
  const rows = items.map((t) => {
    const project = t.project_name ? escapeHtml(t.project_name) : '—';
    const title = escapeHtml(t.title);
    const reason = t.blocked_reason ? escapeHtml(t.blocked_reason) : '—';
    return [project, `<div style="font-weight:600;">${title}</div>`, `<div>${reason}</div>`];
  });

  const body = table(['Projecte', 'Tasca', 'Motiu'], rows);
  return card('Bloquejades', body);
}

function renderPanelActiveProjects(items: ProjectWithNext[]): string {
  const rows = items.map((p) => {
    const name = `<div style="font-weight:700;">${escapeHtml(p.project_name)}</div>` + (p.category_name ? `<div style="opacity:.75;font-size:12px;">${escapeHtml(p.category_name)}</div>` : '');

    const next = p.next_task_title ? `<div style="font-weight:600;">${escapeHtml(p.next_task_title)}</div>` + `<div style="margin-top:4px;">${badge(`P${priorityLabel(p.next_task_priority ?? 3)}`)} ${badge(statusLabel(p.next_task_status ?? 1))}</div>` + (p.blocked_reason ? `<div style="margin-top:6px;opacity:.8;">${badge('Bloqueig')} ${escapeHtml(p.blocked_reason)}</div>` : '') : `<div style="opacity:.7;">Sense NEXT definit</div>`;

    const pr = badge(`Prioritat: ${priorityLabel(p.project_priority)}`);

    return [name, next, pr];
  });

  const body = table(['Projecte', 'Next task', 'Projecte'], rows);
  return card('Projectes actius', body);
}

function renderHomePanels(api: HomeApi): void {
  const root = qs<HTMLDivElement>('#projectesHomePanels');
  const active = qs<HTMLDivElement>('#panelProjectesActius');

  if (!root || !active) return;

  const todayHtml = renderPanelToday(api.today ?? []);
  const blockedHtml = renderPanelBlocked(api.blocked ?? []);

  // Layout 2 columnas (si no usas Bootstrap, esto funciona igual)
  root.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      ${todayHtml}
      ${blockedHtml}
    </div>
  `;

  active.innerHTML = renderPanelActiveProjects(api.activeProjects ?? []);
}

async function fetchHome(): Promise<HomeApi> {
  // Ajusta la ruta base según tu intranet
  const res = await fetch(`${(window as any).API_BASE ?? ''}/api/projectes/home.php`, {
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });

  if (!res.ok) {
    const txt = await res.text().catch(() => '');
    throw new Error(`Home API failed: ${res.status} ${txt}`);
  }

  return (await res.json()) as HomeApi;
}

export async function initProjectesHome(): Promise<void> {
  try {
    const api = await fetchHome();
    renderHomePanels(api);
  } catch (e) {
    console.error(e);
    const root = qs<HTMLDivElement>('#projectesHomePanels');
    if (root) {
      root.innerHTML = card('Error', `<div style="opacity:.8;">No s'han pogut carregar els panells.</div>`);
    }
  }
}
