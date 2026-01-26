import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_IMG } from '../../utils/urls';

interface EducacioI18n {
  id: number;
  educacio_id: number;
  locale: number;
  grau: string;
  notes?: string | null;
}

interface Educacio {
  id: number;
  institucio: string;
  institucio_url?: string | null;
  institucio_localitzacio?: string | null;
  data_inici?: string | null;
  data_fi?: string | null;
  logo_id?: number | null;
  posicio: number;
  visible: number | boolean;
  created_at: string;
  updated_at: string;

  nameImg?: string | null;
  ciutat?: string | null;
  pais_ca?: string | null;

  i18n: EducacioI18n[];
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

const esc = (s: unknown) =>
  String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

const spinner = () => `<div class="d-flex align-items-center"><div class="spinner-border me-2" role="status"></div> Carregant…</div>`;

const LOCALES: Record<number, string> = {
  1: 'Català',
  2: 'English',
  3: 'Castellano',
  4: 'Italiano',
};

function capitalizeFirst(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

function fmtDate(dateStr?: string | null): string {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return '';
  return `${capitalizeFirst(d.toLocaleDateString('ca-ES', { month: 'long' }))} ${d.getFullYear()}`;
}

// 🔑 Pestañas dinámicas sin Bootstrap
function renderTabs(ed: Educacio): string {
  if (!ed.i18n?.length) {
    return `<div class="alert alert-secondary">No hi ha traduccions disponibles.</div>`;
  }

  const tabs = ed.i18n
    .map(
      (t, idx) => `
      <button class="tab-btn ${idx === 0 ? 'active' : ''}" data-target="pane-${t.locale}">
        ${LOCALES[t.locale] ?? 'Idioma ' + t.locale}
      </button>
    `
    )
    .join('');

  const panes = ed.i18n
    .map((t, idx) => {
      const editHref = `https://elliot.cat/gestio/curriculum/modifica-educacio-i18n/${t.locale}/${ed.id}`;
      return `
        <div class="tab-pane ${idx === 0 ? 'active' : ''}" id="pane-${t.locale}">
          <h3>${esc(t.grau)}</h3>
          ${t.notes ? `<p>${esc(t.notes)}</p>` : ''}
          <div class="text-end mt-3">
            <a class="btn btn-sm btn-outline-primary" href="${esc(editHref)}">Modifica</a>
          </div>
        </div>
      `;
    })
    .join('');

  return `
    <div class="tabs-container">
      <div class="tabs-header">${tabs}</div>
      <div class="tabs-body">${panes}</div>
    </div>
  `;
}

function renderEducacio(ed: Educacio): string {
  const logoUrl = ed.nameImg ? `${DOMAIN_IMG}/img/logos-empreses/${ed.nameImg}.png` : null;

  const localitzacio = [ed.ciutat, ed.pais_ca].filter(Boolean).join(', ');

  const periode = ed.data_inici ? `${fmtDate(ed.data_inici)} - ${ed.data_fi ? fmtDate(ed.data_fi) : 'actualitat'}` : '';

  return `
    <div class="mb-3">
      <div class="d-flex align-items-center mb-3">
        ${logoUrl ? `<img src="${esc(logoUrl)}" alt="" style="height:40px" class="me-3">` : ''}
        <div>
          <h2 class="h5 mb-0">${esc(ed.institucio)}</h2>
          ${ed.institucio_url ? `<a href="${esc(ed.institucio_url)}" target="_blank">${esc(ed.institucio_url)}</a>` : ''}
        </div>
      </div>
      ${localitzacio ? `<p class="text-muted mb-2">${esc(localitzacio)}</p>` : ''}
      ${periode ? `<p class="text-muted mb-2">${esc(periode)}</p>` : ''}
      ${renderTabs(ed)}
    </div>
  `;
}

function initTabs(root: HTMLElement) {
  const buttons = root.querySelectorAll<HTMLButtonElement>('.tab-btn');
  const panes = root.querySelectorAll<HTMLElement>('.tab-pane');

  buttons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const targetId = btn.dataset.target;
      if (!targetId) return;

      buttons.forEach((b) => b.classList.remove('active'));
      panes.forEach((p) => p.classList.remove('active'));

      btn.classList.add('active');
      const pane = root.querySelector<HTMLElement>(`#${targetId}`);
      if (pane) {
        pane.classList.add('active');
      }
    });
  });
}

export async function vistaEducacioDetall(id: number): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;
  root.innerHTML = spinner();

  try {
    const url = API_URLS.GET.EDUCACIO_I18N_DETALL_ID(id);
    const res = await fetchDataGet<ApiResponse<Educacio>>(url, true);

    if (res) {
      if (res.status !== 'success') {
        root.innerHTML = `<div class="alert alert-danger">${esc(res.message)}</div>`;
        return;
      }

      root.innerHTML = renderEducacio(res.data);
      initTabs(root);
    }
  } catch (e: any) {
    root.innerHTML = `<div class="alert alert-danger">${esc(e?.message ?? 'Error carregant dades')}</div>`;
  }
}
