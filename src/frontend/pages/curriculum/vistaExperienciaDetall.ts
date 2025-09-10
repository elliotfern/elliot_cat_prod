import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_IMG } from '../../utils/urls';

interface ExperienciaI18n {
  locale: number;
  rol_titol: string;
  sumari?: string | null;
  fites?: string | null;
}

interface Experiencia {
  id: number;
  empresa: string;
  empresa_url?: string | null;
  empresa_localitzacio?: string | null;
  data_inici: string;
  data_fi?: string | null;
  is_current: number | boolean;
  logo_empresa?: number | null;
  posicio: number;
  visible: number | boolean;
  created_at: string;
  updated_at: string;
  idi18n: number;

  nameImg?: string | null;
  city?: string | null;
  pais_cat?: string | null;

  i18n: ExperienciaI18n[];
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

const LOCALE_CODES: Record<number, string> = {
  1: 'ca-ES',
  2: 'en-US',
  3: 'es-ES',
  4: 'it-IT',
};

const CURRENT_LABEL: Record<number, string> = {
  1: 'actualitat',
  2: 'current',
  3: 'actualidad',
  4: 'attuale',
};

function capitalizeFirst(s: string): string {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

function fmtDateLocale(dateStr?: string | null, locale: number = 1): string {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr ?? '';

  const lang = LOCALE_CODES[locale] ?? 'ca-ES';
  const mes = d.toLocaleDateString(lang, { month: 'long' });
  const any = d.toLocaleDateString(lang, { year: 'numeric' });

  return `${capitalizeFirst(mes)} ${any}`;
}

function fmtPeriode(exp: Experiencia, locale: number): string {
  if (exp.is_current === 1 || exp.is_current === true) {
    return `${fmtDateLocale(exp.data_inici, locale)} - ${CURRENT_LABEL[locale] ?? 'actual'}`;
  }
  return `${fmtDateLocale(exp.data_inici, locale)} - ${fmtDateLocale(exp.data_fi, locale)}`;
}

function renderTabs(exp: Experiencia): string {
  if (!exp.i18n?.length) {
    return `<div class="alert alert-secondary">No hi ha traduccions disponibles.</div>`;
  }

  const tabs = exp.i18n
    .map(
      (t, idx) => `
      <button class="tab-btn ${idx === 0 ? 'active' : ''}" data-target="pane-${t.locale}">
        ${LOCALES[t.locale] ?? 'Idioma ' + t.locale}
      </button>
    `
    )
    .join('');

  const panes = exp.i18n
    .map((t, idx) => {
      const editHref = `https://elliot.cat/gestio/curriculum/modifica-experiencia-i18n/${exp.idi18n}`;
      return `
        <div class="tab-pane ${idx === 0 ? 'active' : ''}" id="pane-${t.locale}">
          <h3>${esc(t.rol_titol)}</h3>
          <p class="text-muted mb-2">${fmtPeriode(exp, t.locale)}</p>
          ${t.sumari ? `<p>${esc(t.sumari)}</p>` : ''}
          ${t.fites ? `<div>${t.fites}</div>` : ''}
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

function renderExperiencia(exp: Experiencia): string {
  const logoUrl = exp.nameImg ? `${DOMAIN_IMG}/img/logos-empreses/${exp.nameImg}.png` : null;
  const localitzacio = [exp.city, exp.pais_cat].filter(Boolean).join(', ');

  return `
    <div class="mb-3">
      <div class="d-flex align-items-center mb-3">
        ${logoUrl ? `<img src="${esc(logoUrl)}" alt="" style="height:40px" class="me-3">` : ''}
        <div>
          <h2 class="h5 mb-0">${esc(exp.empresa)}</h2>
          ${exp.empresa_url ? `<a href="${esc(exp.empresa_url)}" target="_blank">${esc(exp.empresa_url)}</a>` : ''}
        </div>
      </div>
      ${localitzacio ? `<p class="text-muted mb-2">${esc(localitzacio)}</p>` : ''}
      ${renderTabs(exp)}
    </div>
  `;
}

/** Inicializa el comportamiento de pestañas sin Bootstrap */
function initTabs(root: HTMLElement) {
  const buttons = root.querySelectorAll<HTMLButtonElement>('.tab-btn');
  const panes = root.querySelectorAll<HTMLElement>('.tab-pane');

  buttons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const targetId = btn.dataset.target;
      if (!targetId) return;

      // desactivar todo
      buttons.forEach((b) => b.classList.remove('active'));
      panes.forEach((p) => p.classList.remove('active'));

      // activar botón
      btn.classList.add('active');
      // activar panel correspondiente
      const pane = root.querySelector<HTMLElement>(`#${targetId}`);
      if (pane) {
        pane.classList.add('active');
      }
    });
  });
}

export async function vistaExperienciaDetall(id: number): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;
  root.innerHTML = spinner();

  try {
    const url = API_URLS.GET.EXPERIENCIA_I18N_DETALL_ID(id);
    const res = await fetchDataGet<ApiResponse<Experiencia>>(url, true);

    if (res) {
      if (res.status !== 'success') {
        root.innerHTML = `<div class="alert alert-danger">${esc(res.message)}</div>`;
        return;
      }

      root.innerHTML = renderExperiencia(res.data);
      initTabs(root); // 👉 inicializar las pestañas
    }
  } catch (e: any) {
    root.innerHTML = `<div class="alert alert-danger">${esc(e?.message ?? 'Error carregant dades')}</div>`;
  }
}
