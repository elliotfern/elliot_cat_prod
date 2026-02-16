// src/frontend/pages/blog/llistatArticlesPaged.ts
// Llistat d'articles del blog amb:
// - paginació clàssica (Prev / Next)
// - filtres server-side: any (year) i categoria (cat=hex o cat=0 per "Sense categoria")
// - filtre server-side: idioma (lang=id)
// - ordre (asc/desc)
// - URL diferent segons admin:
//    admin  -> /gestio/blog/article/<slug>
//    públic -> /blog/article/<slug>
//
// Container esperat: <div id="articleList"></div>

import { getIsAdmin } from '../../services/auth/isAdmin';

type BlogArticle = {
  id: number;
  post_title: string;
  slug: string;
  post_date: string;
  tema_ca?: string | null;
  categoria_hex?: string | null; // ve del backend: HEX(b.categoria)
  lang?: number | null; // idioma ID
};

type ApiPayload = {
  items: BlogArticle[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    pages: number;
    has_prev: boolean;
    has_next: boolean;
  };
};

type BlogFacets = {
  years: number[];
  categories: Array<{ hex: string; label: string }>;
  langs: Array<{ id: number; label: string }>;
};

function escapeHtml(input: unknown): string {
  return String(input ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function formatDateCa(dateStr: string): string {
  const iso = String(dateStr).trim().replace(' ', 'T');
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) {
    const m = String(dateStr).match(/^(\d{4}-\d{2}-\d{2})/);
    return m ? m[1] : String(dateStr);
  }
  return d.toLocaleDateString('ca-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

function parseApiPayload(json: any): ApiPayload {
  // Suporta:
  // - { status, data: { items, pagination } }
  // - { data: { items, pagination } }
  // - { items, pagination }
  const data = json?.data ?? json;

  if (data && Array.isArray(data.items) && data.pagination) return data as ApiPayload;

  // fallback (si tornessis a retornar array)
  if (Array.isArray(data)) {
    return {
      items: data as BlogArticle[],
      pagination: {
        page: 1,
        limit: data.length,
        total: data.length,
        pages: 1,
        has_prev: false,
        has_next: false,
      },
    };
  }

  return {
    items: [],
    pagination: { page: 1, limit: 10, total: 0, pages: 1, has_prev: false, has_next: false },
  };
}

async function fetchPage(params: {
  page: number;
  limit: number;
  order: 'asc' | 'desc';
  year?: number;
  cat?: string; // '' | '0' | hex
  lang?: number; // 0/undefined = tots
}): Promise<ApiPayload> {
  const usp = new URLSearchParams();
  usp.set('page', String(params.page));
  usp.set('limit', String(params.limit));
  usp.set('order', params.order);

  if (params.year && params.year > 0) usp.set('year', String(params.year));
  if (params.cat && params.cat !== '') usp.set('cat', params.cat);
  if (params.lang && params.lang > 0) usp.set('lang', String(params.lang));

  const url = `https://${window.location.host}/api/blog/get/llistatArticles?${usp.toString()}`;

  const r = await fetch(url, { credentials: 'include' });
  const json = await r.json();
  return parseApiPayload(json);
}

async function fetchFacets(): Promise<BlogFacets> {
  const url = `https://${window.location.host}/api/blog/get/filtresArticles`;
  const r = await fetch(url, { credentials: 'include' });
  const json = await r.json();

  const data = (json?.data ?? json) as Partial<BlogFacets>;

  const years = Array.isArray(data.years) ? data.years.map((y) => Number(y)).filter((y) => Number.isFinite(y)) : [];

  const categories = Array.isArray(data.categories)
    ? data.categories
        .filter((c) => c && typeof (c as any).hex === 'string' && typeof (c as any).label === 'string')
        .map((c) => ({ hex: (c as any).hex.trim(), label: (c as any).label.trim() }))
        .filter((c) => c.hex !== '' && c.label !== '')
    : [];

  const langs = Array.isArray((data as any).langs)
    ? (data as any).langs
        .filter((x: any) => x && Number.isFinite(Number(x.id)) && typeof x.label === 'string')
        .map((x: any) => ({ id: Number(x.id), label: String(x.label).trim() }))
        .filter((x: any) => x.id > 0 && x.label !== '')
    : [];

  return { years, categories, langs };
}

export async function renderBlogListPaged(): Promise<void> {
  const container = document.getElementById('articleList');
  if (!container) return;

  // ✅ Óptimo: calculamos 1 sola vez
  const isAdmin = await getIsAdmin();

  // ✅ URL según rol
  function buildArticleUrl(slug: string): string {
    const safe = encodeURIComponent(slug);
    return isAdmin ? `/gestio/blog/article/${safe}` : `/blog/article/${safe}`;
  }

  const state = {
    page: 1,
    limit: 10,
    year: 0, // 0 = tots
    cat: '', // '' = totes, '0' = sense categoria, hex = categoria
    lang: 0, // 0 = tots, >0 = idioma id
    order: 'desc' as 'asc' | 'desc',
  };

  container.innerHTML = `
    <div class="d-flex flex-column gap-3">
      <div class="card">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-3">
              <label class="form-label mb-1" for="blogYearSelect">Any</label>
              <select id="blogYearSelect" class="form-select">
                <option value="0">Tots</option>
              </select>
            </div>

            <div class="col-12 col-lg-4">
              <label class="form-label mb-1" for="blogCatSelect">Categoria</label>
              <select id="blogCatSelect" class="form-select">
                <option value="">Totes</option>
                <option value="0">Sense categoria</option>
              </select>
            </div>

            <div class="col-12 col-lg-3">
              <label class="form-label mb-1" for="blogLangSelect">Idioma</label>
              <select id="blogLangSelect" class="form-select">
                <option value="0">Tots</option>
              </select>
            </div>

            <div class="col-12 col-lg-2">
              <label class="form-label mb-1" for="blogOrderSelect">Ordre</label>
              <select id="blogOrderSelect" class="form-select">
                <option value="desc" selected>Més nous</option>
                <option value="asc">Més antics</option>
              </select>
            </div>
          </div>

          <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-muted" id="blogCountInfo">—</div>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="blogResetBtn">
              Neteja filtres
            </button>
          </div>
        </div>
      </div>

      <div id="blogListWrap"></div>

      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <button class="btn btn-outline-secondary" type="button" id="blogPrevBtn">← Anterior</button>
        <div class="text-muted" id="blogPageInfo">—</div>
        <button class="btn btn-outline-secondary" type="button" id="blogNextBtn">Següent →</button>
      </div>
    </div>
  `;

  const yearSelect = container.querySelector<HTMLSelectElement>('#blogYearSelect')!;
  const catSelect = container.querySelector<HTMLSelectElement>('#blogCatSelect')!;
  const langSelect = container.querySelector<HTMLSelectElement>('#blogLangSelect')!;
  const orderSelect = container.querySelector<HTMLSelectElement>('#blogOrderSelect')!;
  const listWrap = container.querySelector<HTMLDivElement>('#blogListWrap')!;
  const countInfo = container.querySelector<HTMLDivElement>('#blogCountInfo')!;
  const pageInfo = container.querySelector<HTMLDivElement>('#blogPageInfo')!;
  const prevBtn = container.querySelector<HTMLButtonElement>('#blogPrevBtn')!;
  const nextBtn = container.querySelector<HTMLButtonElement>('#blogNextBtn')!;
  const resetBtn = container.querySelector<HTMLButtonElement>('#blogResetBtn')!;

  let facetsLoaded = false;

  async function ensureFacets(): Promise<void> {
    if (facetsLoaded) return;

    const facets = await fetchFacets();

    // YEARS
    const years = Array.from(new Set(facets.years))
      .filter((y) => y >= 1970 && y <= 2100)
      .sort((a, b) => b - a);

    yearSelect.innerHTML = [`<option value="0">Tots</option>`, ...years.map((y) => `<option value="${y}">${y}</option>`)].join('');

    // CATEGORIES (HEX)
    const categories = facets.categories.slice().sort((a, b) => a.label.localeCompare(b.label, 'ca'));

    catSelect.innerHTML = [`<option value="">Totes</option>`, `<option value="0">Sense categoria</option>`, ...categories.map((c) => `<option value="${escapeHtml(c.hex)}">${escapeHtml(c.label)}</option>`)].join('');

    // LANGS (ID)
    const langs = facets.langs.slice().sort((a, b) => a.label.localeCompare(b.label, 'ca'));

    langSelect.innerHTML = [`<option value="0">Tots</option>`, ...langs.map((l) => `<option value="${l.id}">${escapeHtml(l.label)}</option>`)].join('');

    facetsLoaded = true;
  }

  function renderList(items: BlogArticle[]): void {
    if (!items.length) {
      listWrap.innerHTML = `<div class="alert alert-secondary mb-0">No hi ha articles.</div>`;
      return;
    }

    const rowsHtml = items
      .map((row) => {
        const href = buildArticleUrl(row.slug);

        const title = escapeHtml(row.post_title || '(Sense títol)');
        const cat = escapeHtml((row.tema_ca ?? 'Sense categoria') || 'Sense categoria');
        const dateLabel = escapeHtml(formatDateCa(row.post_date));

        return `
          <a class="list-group-item list-group-item-action" href="${href}">
            <div class="d-flex flex-column flex-md-row gap-1 gap-md-3 align-items-md-center justify-content-between">
              <div class="d-flex flex-column">
                <div class="fw-semibold">${title}</div>
                <div class="text-muted small">${dateLabel} · <span class="badge text-bg-light border">${cat}</span></div>
              </div>
              <div class="text-muted small d-none d-md-block">→</div>
            </div>
          </a>
        `;
      })
      .join('');

    listWrap.innerHTML = `<div class="list-group">${rowsHtml}</div>`;
  }

  async function load(): Promise<void> {
    await ensureFacets();

    // UX: desactiva mentre carrega
    prevBtn.disabled = true;
    nextBtn.disabled = true;

    const data = await fetchPage({
      page: state.page,
      limit: state.limit,
      order: state.order,
      year: state.year > 0 ? state.year : undefined,
      cat: state.cat !== '' ? state.cat : undefined,
      lang: state.lang > 0 ? state.lang : undefined,
    });

    const items = data.items ?? [];
    const pag = data.pagination;

    renderList(items);

    const total = pag?.total ?? items.length;
    const pages = pag?.pages ?? 1;
    const page = pag?.page ?? state.page;

    countInfo.textContent = `Mostrant ${items.length} de ${total}`;
    pageInfo.textContent = `Pàgina ${page} de ${pages}`;

    prevBtn.disabled = !(pag?.has_prev ?? page > 1);
    nextBtn.disabled = !(pag?.has_next ?? page < pages);

    // sincronitza selects
    yearSelect.value = String(state.year);
    catSelect.value = state.cat;
    langSelect.value = String(state.lang);
    orderSelect.value = state.order;
  }

  // Events filtres
  yearSelect.addEventListener('change', () => {
    state.year = parseInt(yearSelect.value, 10) || 0;
    state.page = 1;
    load();
  });

  catSelect.addEventListener('change', () => {
    state.cat = catSelect.value; // '' | '0' | hex
    state.page = 1;
    load();
  });

  langSelect.addEventListener('change', () => {
    state.lang = parseInt(langSelect.value, 10) || 0; // 0 | id
    state.page = 1;
    load();
  });

  orderSelect.addEventListener('change', () => {
    state.order = orderSelect.value === 'asc' ? 'asc' : 'desc';
    state.page = 1;
    load();
  });

  resetBtn.addEventListener('click', () => {
    state.page = 1;
    state.year = 0;
    state.cat = '';
    state.lang = 0;
    state.order = 'desc';

    yearSelect.value = '0';
    catSelect.value = '';
    langSelect.value = '0';
    orderSelect.value = 'desc';

    load();
  });

  // Events paginació
  prevBtn.addEventListener('click', () => {
    if (state.page > 1) {
      state.page -= 1;
      load();
    }
  });

  nextBtn.addEventListener('click', () => {
    state.page += 1;
    load();
  });

  // init
  load();
}
