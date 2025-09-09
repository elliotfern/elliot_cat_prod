// src/pages/curriculum/vistaPerfilCVI18n.ts
import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';

interface PerfilCVI18n {
  id: number;
  perfil_id: number;
  locale: number; // 1=ca, 3=es, 2=en, 4=it
  titular: string;
  sumari: string;
}

interface ApiResponse<T> {
  status: 'success' | 'error';
  message: string;
  data: T;
}

const LOCALES = [
  { id: 1, code: 'ca', label: 'Català' },
  { id: 3, code: 'es', label: 'Castellà' },
  { id: 2, code: 'en', label: 'Anglès' },
  { id: 4, code: 'it', label: 'Italià' },
] as const;

const esc = (s: unknown) =>
  String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const nl2br = (s: string) => esc(s).replace(/\n/g, '<br>');

const spinner = () => `<div class="d-flex align-items-center"><div class="spinner-border me-2" role="status" aria-hidden="true"></div> Carregant…</div>`;

const errorBox = (msg: string) => `<div class="alert alert-danger" role="alert">${esc(msg)}</div>`;

function layoutHTML(perfilId: number) {
  const tabs = LOCALES.map(
    (l, i) =>
      `<button class="nav-link ${i === 0 ? 'active' : ''}" data-locale="${l.id}" type="button" role="tab" aria-selected="${i === 0}">
       ${esc(l.label)}
     </button>`
  ).join('');

  return `
    <ul class="nav nav-tabs mb-3" role="tablist">${tabs}</ul>
    <div id="tabContent" class="card">
      <div class="card-body">
        <div id="localeContent">${spinner()}</div>
      </div>
      <div class="card-footer text-muted small">
        Perfil ID: ${perfilId}
      </div>
    </div>
  `;
}

export async function vistaPerfilCVi18n(perfilId = 1): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;

  // Dibuja UI base
  root.innerHTML = layoutHTML(perfilId);

  const content = root.querySelector('#localeContent') as HTMLElement;
  const tabBar = root.querySelector('.nav-tabs') as HTMLElement;

  // Cache por locale
  const cache = new Map<number, PerfilCVI18n>();

  // Locale inicial (query ?locale=… o catalán)
  const qs = new URLSearchParams(window.location.search);
  const initial = Number(qs.get('locale')) || 1;

  const setActive = (locale: number) => {
    tabBar.querySelectorAll<HTMLButtonElement>('.nav-link').forEach((btn) => {
      const isActive = Number(btn.dataset.locale) === locale;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-selected', String(isActive));
    });
  };

  const render = (d: PerfilCVI18n) => {
    content.innerHTML = `
      <h2 class="h4 mb-2">${esc(d.titular)}</h2>
      <div class="text-body">${nl2br(d.sumari)}</div>
      <hr>
      <div class="text-muted small">Locale: ${d.locale} · Registre ID: ${d.id}</div>
    `;
  };

  const loadLocale = async (locale: number) => {
    setActive(locale);
    // cache
    if (cache.has(locale)) {
      render(cache.get(locale)!);
      return;
    }
    content.innerHTML = spinner();
    try {
      const url = API_URLS.GET.PERFIL_CV_I18N_ID(perfilId, locale);
      const res = await fetchDataGet<ApiResponse<PerfilCVI18n>>(url, true);
      if (res) {
        if (res.status !== 'success' || !res.data) {
          content.innerHTML = errorBox(res.message || 'No s\'han trobat dades per a aquest idioma.');
          return;
        }
        cache.set(locale, res.data);
        render(res.data);
      }
    } catch (e: any) {
      content.innerHTML = errorBox(e?.message ?? 'Error carregant les dades');
    }
  };

  // Click handlers
  tabBar.addEventListener('click', (ev) => {
    const btn = (ev.target as HTMLElement).closest<HTMLButtonElement>('.nav-link');
    if (!btn) return;
    const locale = Number(btn.dataset.locale);
    if (!Number.isFinite(locale)) return;
    loadLocale(locale);
  });

  // Carga inicial
  await loadLocale(initial);
}
