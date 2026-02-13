import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';
import { formatData } from '../../utils/formataData';

// --- Types ---
type ProjecteDetalls = {
  id: number;
  name: string;
  description: string | null;
  status: number;
  category_id: number | null;
  category_name?: string | null;
  start_date: string;
  end_date: string;
  priority: number;
  client_id: number | null;
  client_name?: string | null;
  budget_id: number | null;
  invoice_id: number | null;
  created_at?: string;
  updated_at?: string;
};

type TascaItem = {
  id: number;
  project_id: number | null;
  title: string;
  subject: string | null;
  notes: string | null;
  status: number;
  priority: number;
  planned_date: string;
  is_next: number;
  blocked_reason: string | null;
  estimated_hours: string | number | null;
  created_at?: string;
  updated_at?: string;
  done_at?: string | null;
};

type TasquesResponse = {
  project: { id: number };
  kpis: {
    total: number;
    done: number;
    blocked: number;
    in_progress: number;
    backlog: number;
    next: number;
  };
  page: number;
  limit: number;
  items: TascaItem[];
};

type ApiResponse<T> = {
  status: string;
  message: string;
  data: T;
};

// --- Helpers UI ---
function setText(id: string, value: string) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = value;
}

function labelStatus(status: number): string {
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
      return String(status);
  }
}

function labelPriority(p: number): string {
  switch (p) {
    case 1:
      return '1 - Baixa';
    case 2:
      return '2 - Mitja';
    case 3:
      return '3 - Alta';
    case 4:
      return '4 - Urgent';
    default:
      return String(p);
  }
}

function escapeHtml(s: string): string {
  return s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
}

export async function initProjecteDetalls(id: number): Promise<void> {
  const meta = document.getElementById('projecteDetallsMeta') as HTMLDivElement | null;
  if (!meta) {
    return;
  }

  const projectId = id;

  if (!Number.isFinite(projectId) || projectId <= 0) {
    return;
  }

  // Containers
  const header = document.getElementById('projecteDetallsHeader');
  const fitxa = document.getElementById('projecteDetallsFitxa');
  const kpisBox = document.getElementById('projecteDetallsKpis');
  const tasquesBox = document.getElementById('projecteDetallsTasques');

  if (!header || !fitxa || !kpisBox || !tasquesBox) {
    return;
  }

  // --- 1) GET detalls projecte ---
  const resP = await fetchDataGet<ApiResponse<ProjecteDetalls>>(API_URLS.GET.PROJECTE_DETALLS(projectId), true);
  if (!resP || !resP.data) {
    return;
  }

  const p = resP.data;

  // Pintar Header (básico)
  header.innerHTML = `
    <div class="d-flex align-items-start justify-content-between">
      <div>
        <h3 class="mb-1">${escapeHtml(p.name ?? '—')}</h3>
        <div class="text-muted small">
          #${p.id}
          ${p.category_name ? ` · ${escapeHtml(String(p.category_name))}` : ''}
        </div>
      </div>
      <div class="text-muted small">
        Estat: <strong>${escapeHtml(labelStatus(p.status))}</strong>
        · Prioritat: <strong>${escapeHtml(labelPriority(p.priority))}</strong>
      </div>
    </div>
  `;

  // Pintar Fitxa (básico)
  fitxa.innerHTML = `
    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div class="small text-muted">Data inici</div>
        <div>${escapeHtml(formatData(p.start_date))}</div>
      </div>
      <div class="col-12 col-md-6">
        <div class="small text-muted">Data fi</div>
        <div>${escapeHtml(formatData(p.end_date))}</div>
      </div>

      <div class="col-12">
        <div class="small text-muted">Descripció</div>
        <div>${p.description ? escapeHtml(p.description) : '—'}</div>
      </div>
    </div>
  `;

  // Subtítulo fijo (si quieres)
  setText('subtitolProjecte', `Detalls del projecte · #${p.id}`);

  // --- 2) GET tasques + KPIs ---
  const resT = await fetchDataGet<ApiResponse<TasquesResponse>>(API_URLS.GET.PROJECTE_TASQUES(projectId), true);
  if (!resT || !resT.data) {
    // No es fatal: dejamos proyecto pintado
    kpisBox.innerHTML = `<div class="text-muted">No s'han pogut carregar les tasques.</div>`;
    tasquesBox.innerHTML = '';
    return;
  }

  const { kpis, items } = resT.data;

  // KPIs (simple)
  kpisBox.innerHTML = `
    <div class="d-flex flex-wrap gap-3">
      <div><strong>${kpis.total}</strong> total</div>
      <div><strong>${kpis.done}</strong> fetes</div>
      <div><strong>${kpis.blocked}</strong> bloquejades</div>
      <div><strong>${kpis.in_progress}</strong> en curs</div>
      <div><strong>${kpis.backlog}</strong> backlog</div>
      <div><strong>${kpis.next}</strong> next</div>
    </div>
  `;

  // --- 3) Tabla de tareas (render muy básico) ---
  // Si ya tienes tu renderDynamicTable que acepta URL, lo ideal es que el endpoint devuelva items “directo”.
  // Como aquí ya tenemos items, lo más simple es pintar HTML.
  // (Luego lo refinamos con tu tabla dinámica si quieres.)

  tasquesBox.innerHTML = `
    <div class="table-responsive">
      <table class="table table-striped">
        <thead class="table-primary">
          <tr>
            <th>Títol</th>
            <th>Estat</th>
            <th>Prioritat</th>
            <th>Data</th>
            <th>Next</th>
            <th style="width:160px"></th>
          </tr>
        </thead>
        <tbody>
          ${
            items.length
              ? items
                  .map(
                    (t) => `
                <tr>
                  <td>${escapeHtml(t.title ?? '')}</td>
                  <td>${escapeHtml(labelStatus(Number(t.status)))}</td>
                  <td>${escapeHtml(labelPriority(Number(t.priority)))}</td>
                  <td>${escapeHtml(formatData(t.planned_date))}</td>
                  <td>${Number(t.is_next) === 1 ? '✅' : ''}</td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary"
                       href="/gestio/projectes/modifica-tasca/${t.id}">
                      Edita
                    </a>
                  </td>
                </tr>
              `
                  )
                  .join('')
              : `<tr><td colspan="6" class="text-muted">No hi ha tasques.</td></tr>`
          }
        </tbody>
      </table>
    </div>
  `;
}
