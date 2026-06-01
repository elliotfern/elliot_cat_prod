import { api } from '../../core/api/client';
import { PerfilCVI18n } from '../../types/Curriculum';
import { API_URLS } from '../../utils/apiUrls';

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

const localeLabel = (loc: number) => LOCALES.find((l) => l.id === loc)?.label ?? `Locale ${loc}`;

// URL del botón de edición
const editUrl = (locale: number) => `${window.location.origin}/gestio/curriculum/modifica-perfil-i18n/1/${locale}`;

function layoutHTML(perfilId: number) {
  const tabs = LOCALES.map(
    (l, i) =>
      `<button class="nav-link ${i === 0 ? 'active' : ''}" data-locale="${l.id}" type="button" role="tab" aria-selected="${i === 0}">
       ${esc(l.label)}
     </button>`
  ).join('');

  return `
    <ul class="nav nav-tabs mb-3" role="tablist">${tabs}</ul>
    <div id="tabContent">
      <div class="card-body">
        <div id="localeContent">${spinner()}</div>
      </div>
    </div>
  `;
}

export async function vistaPerfilCVi18n(id = 1): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;

  // Dibuja UI base
  root.innerHTML = layoutHTML(id);

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
    const urlEdit = editUrl(d.locale);
    content.innerHTML = `
      <h2 class="h4 mb-2">${esc(d.titular)}</h2>
      <div class="text-body">${nl2br(d.sumari)}</div>

      <div class="mt-3 text-end">
        <a class="btn btn-sm btn-outline-primary" href="${esc(urlEdit)}"
           aria-label="Editar ${esc(localeLabel(d.locale))}">
          ✏️ Edita (${esc(localeLabel(d.locale))})
        </a>
      </div>
      <hr>
      <div class="text-muted small">Registre ID: ${d.id}</div>
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

    let data: PerfilCVI18n;
    try {
      data = await api.get<PerfilCVI18n>(API_URLS.GET.PERFIL_CV_I18N_ID, {
        id,
        locale,
      });
      cache.set(locale, data);
      render(data);
    } catch (error) {
      console.error(error);
      content.innerHTML = errorBox("No s'han trobat dades per a aquest idioma.");
      return;
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
