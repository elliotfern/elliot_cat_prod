// src/frontend/pages/blog/llistatHistoriaObertaPaged.ts
// Llistat intern (intranet) d'articles post_type="historia_oberta" amb:
// - paginació (Prev/Next)
// - filtres server-side: curs, idioma, status, ordre
// - badges: curs + idioma + status
// - accions: veure públic + editar fitxa (gestio)
//
// Container esperat: <div id="articleList"></div>

import { langIdToCode } from '../../utils/locales/getLangPrefix';

type HistoriaObertaRow = {
  blog_id: number;
  group_id: number;
  curs_id: number;
  curs_ordre: number;
  article_ordre: number;
  course_name: string;
  lang: number;
  post_status?: string | null;
  slug: string;
  post_title: string;
  post_date: string;
  post_modified?: string | null;
};

type ApiPayload = {
  items: HistoriaObertaRow[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    pages: number;
    has_prev: boolean;
    has_next: boolean;
  };
  filters?: {
    curs?: number | null;
    lang?: number | null;
    status?: string | null;
    order?: 'asc' | 'desc';
  };
};

type HistoriaObertaFacets = {
  courses: Array<{ id: number; ordre?: number; label: string }>;
  langs: Array<{ id: number; label: string }>;
  statuses: string[];
  uiLang?: string;
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

function normalizeStatus(status?: string | null): string {
  return String(status ?? '').trim().toLowerCase();
}

function renderStatusBadge(status?: string | null): string {
  const s = normalizeStatus(status);

  if (s === 'draft' || s === 'borrador' || s === 'esborrany') {
    return `<span class="badge text-bg-warning">Esborrany</span>`;
  }

  if (s === 'publish' || s === 'published' || s === 'publicat') {
    return `<span class="badge text-bg-success">Publicat</span>`;
  }

  if (s === 'cancelat' || s === 'cancel·lat' || s === 'cancelled') {
    return `<span class="badge text-bg-danger">Cancel·lat</span>`;
  }

  if (!s) return `<span class="badge text-bg-secondary">—</span>`;

  return `<span class="badge text-bg-secondary">${escapeHtml(s)}</span>`;
}

function renderLangBadge(langId: number, langsMap: Map<number, string>): string {
  const label = langsMap.get(langId) ?? `Lang ${langId}`;
  return `<span class="badge text-bg-light border">${escapeHtml(label)}</span>`;
}

function buildEditUrl(blogId: number): string {
  return `/gestio/blog/modifica-article/${encodeURIComponent(String(blogId))}`;
}

function buildPublicUrl(slug: string, langId: number): string {
  const safe = encodeURIComponent(String(slug ?? ''));
  const code = langIdToCode(langId);
  return `/${code}/historia/article/${safe}`;
}

function parseApiPayload(json: any): ApiPayload {
  const data = json?.data ?? json;

  if (data && Array.isArray(data.items) && data.pagination) return data as ApiPayload;

  if (Array.isArray(data)) {
    return {
      items: data as HistoriaObertaRow[],
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
    pagination: { page: 1, limit: 20, total: 0, pages: 1, has_prev: false, has_next: false },
  };
}

async function fetchFacets(): Promise<HistoriaObertaFacets> {
  const url = `https://${window.location.host}/api/blog/get/filtresHistoriaOberta`;
  const r = await fetch(url, { credentials: 'include' });
  const json = await r.json();
  const data = (json?.data ?? json) as Partial<HistoriaObertaFacets>;

  const courses = Array.isArray(data.courses)
    ? data.courses
        .filter((c) => c && Number.isFinite(Number((c as any).id)) && typeof (c as any).label === 'string')
        .map((c) => ({
          id: Number((c as any).id),
          ordre: Number((c as any).ordre ?? 0),
          label: String((c as any).label).trim(),
        }))
        .filter((c) => c.id > 0 && c.label !== '')
    : [];

  const langs = Array.isArray(data.langs)
    ? data.langs
        .filter((l) => l && Number.isFinite(Number((l as any).id)) && typeof (l as any).label === 'string')
        .map((l) => ({ id: Number((l as any).id), label: String((l as any).label).trim() }))
        .filter((l) => l.id > 0 && l.label !== '')
    : [];

  const statuses = Array.isArray(data.statuses)
    ? data.statuses.map((s) => String(s).trim()).filter((s) => s !== '')
    : [];

  return {
    courses,
    langs,
    statuses: Array.from(new Set(statuses)),
    uiLang: typeof data.uiLang === 'string' ? data.uiLang : undefined,
  };
}

async function fetchPage(params: {
  page: number;
  limit: number;
  order: 'asc' | 'desc';
  curs?: number; // 0/undefined = tots
  lang?: number; // 0/undefined = tots
  status?: string; // ''/undefined = tots
}): Promise<ApiPayload> {
  const usp = new URLSearchParams();
  usp.set('page', String(params.page));
  usp.set('limit', String(params.limit));
  usp.set('order', params.order);

  if (params.curs && params.curs > 0) usp.set('curs', String(params.curs));
  if (params.lang && params.lang > 0) usp.set('lang', String(params.lang));
  if (params.status && params.status !== '') usp.set('status', params.status);

  const url = `https://${window.location.host}/api/blog/get/llistatHistoriaOberta?${usp.toString()}`;

  const r = await fetch(url, { credentials: 'include' });
  const json = await r.json();
  return parseApiPayload(json);
}

export async function renderHistoriaObertaListPaged(): Promise<void> {
  const container = document.getElementById('articleList');
  if (!container) return;

  const state = {
    page: 1,
    limit: 20,
    curs: 0,
    lang: 0,
    status: '',
    order: 'asc' as 'asc' | 'desc',
  };

  container.innerHTML = `
    <div class="d-flex flex-column gap-3">
      <div class="card">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
              <label class="form-label mb-1" for="hoCursSelect">Curs</label>
              <select id="hoCursSelect" class="form-select">
                <option value="0">Tots</option>
              </select>
            </div>

            <div class="col-12 col-lg-3">
              <label class="form-label mb-1" for="hoLangSelect">Idioma</label>
              <select id="hoLangSelect" class="form-select">
                <option value="0">Tots</option>
              </select>
            </div>

            <div class="col-12 col-lg-3">
              <label class="form-label mb-1" for="hoStatusSelect">Estat</label>
              <select id="hoStatusSelect" class="form-select">
                <option value="">Tots</option>
              </select>
            </div>

            <div class="col-12 col-lg-2">
              <label class="form-label mb-1" for="hoOrderSelect">Ordre</label>
              <select id="hoOrderSelect" class="form-select">
                <option value="asc" selected>Ordre curs</option>
                <option value="desc">Invertit</option>
              </select>
            </div>
          </div>

          <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-muted" id="hoCountInfo">—</div>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="hoResetBtn">
              Neteja filtres
            </button>
          </div>
        </div>
      </div>

      <div id="hoListWrap"></div>

      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <button class="btn btn-outline-secondary" type="button" id="hoPrevBtn">← Anterior</button>
        <div class="text-muted" id="hoPageInfo">—</div>
        <button class="btn btn-outline-secondary" type="button" id="hoNextBtn">Següent →</button>
      </div>
    </div>
  `;

  const cursSelect = container.querySelector<HTMLSelectElement>('#hoCursSelect')!;
  const langSelect = container.querySelector<HTMLSelectElement>('#hoLangSelect')!;
  const statusSelect = container.querySelector<HTMLSelectElement>('#hoStatusSelect')!;
  const orderSelect = container.querySelector<HTMLSelectElement>('#hoOrderSelect')!;
  const listWrap = container.querySelector<HTMLDivElement>('#hoListWrap')!;
  const countInfo = container.querySelector<HTMLDivElement>('#hoCountInfo')!;
  const pageInfo = container.querySelector<HTMLDivElement>('#hoPageInfo')!;
  const prevBtn = container.querySelector<HTMLButtonElement>('#hoPrevBtn')!;
  const nextBtn = container.querySelector<HTMLButtonElement>('#hoNextBtn')!;
  const resetBtn = container.querySelector<HTMLButtonElement>('#hoResetBtn')!;

  // facets
  const facets = await fetchFacets();

  // map lang label
  const langsMap = new Map<number, string>(facets.langs.map((l) => [l.id, l.label]));

  // COURSES
  const courses = facets.courses
    .slice()
    .sort((a, b) => (Number(a.ordre ?? 0) - Number(b.ordre ?? 0)) || (a.id - b.id));

  cursSelect.innerHTML = [
    `<option value="0">Tots</option>`,
    ...courses.map((c) => `<option value="${c.id}">${escapeHtml(c.label)}</option>`),
  ].join('');

  // LANGS
  const langs = facets.langs.slice().sort((a, b) => a.label.localeCompare(b.label, 'ca'));
  langSelect.innerHTML = [
    `<option value="0">Tots</option>`,
    ...langs.map((l) => `<option value="${l.id}">${escapeHtml(l.label)}</option>`),
  ].join('');

  // STATUSES
  const statuses = facets.statuses.slice().sort((a, b) => a.localeCompare(b, 'ca'));
  statusSelect.innerHTML = [
    `<option value="">Tots</option>`,
    ...statuses.map((s) => `<option value="${escapeHtml(s)}">${escapeHtml(s)}</option>`),
  ].join('');

  function renderList(items: HistoriaObertaRow[]): void {
    if (!items.length) {
      listWrap.innerHTML = `<div class="alert alert-secondary mb-0">No hi ha articles.</div>`;
      return;
    }

    const rowsHtml = items
      .map((row) => {
        const title = escapeHtml(row.post_title || '(Sense títol)');
        const dateLabel = escapeHtml(formatDateCa(row.post_date));
        const courseName = escapeHtml(row.course_name || 'Curs');

        const courseBadge = `<span class="badge text-bg-info">${courseName}</span>`;
        const langBadge = renderLangBadge(Number(row.lang ?? 0), langsMap);
        const statusBadge = renderStatusBadge(row.post_status);

        const publicUrl = buildPublicUrl(row.slug, Number(row.lang ?? 1));
        const editUrl = buildEditUrl(row.blog_id);

        const metaSmall = `
          <span class="text-muted">${dateLabel}</span>
          <span class="text-muted"> · </span>
          ${courseBadge}
          <span> </span>
          ${langBadge}
          <span> </span>
          ${statusBadge}
          <span class="text-muted"> · </span>
          <span class="text-muted">Ordre: ${escapeHtml(String(row.article_ordre ?? ''))}</span>
        `;

        return `
          <div class="list-group-item position-relative">
            <div class="d-flex flex-column flex-md-row gap-2 gap-md-3 align-items-md-center justify-content-between">
              <div class="d-flex flex-column">
                <div class="fw-semibold">${title}</div>
                <div class="small">${metaSmall}</div>
              </div>

              <div class="d-flex align-items-center gap-2 flex-wrap justify-content-md-end position-relative" style="z-index: 2;">
                <a class="btn btn-sm btn-outline-secondary" href="${escapeHtml(publicUrl)}" target="_blank" rel="noopener noreferrer">
                  Veure públic
                </a>

                <a class="btn btn-sm btn-outline-primary" href="${escapeHtml(editUrl)}">
                  <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>
                  Edita
                </a>
              </div>
            </div>
          </div>
        `;
      })
      .join('');

    listWrap.innerHTML = `<div class="list-group">${rowsHtml}</div>`;
  }

  async function load(): Promise<void> {
    prevBtn.disabled = true;
    nextBtn.disabled = true;

    const data = await fetchPage({
      page: state.page,
      limit: state.limit,
      order: state.order,
      curs: state.curs > 0 ? state.curs : undefined,
      lang: state.lang > 0 ? state.lang : undefined,
      status: state.status !== '' ? state.status : undefined,
    });

    const items = data.items ?? [];
    renderList(items);

    const pag = data.pagination;
    const pages = pag?.pages ?? 1;
    const page = pag?.page ?? state.page;
    const total = pag?.total ?? items.length;

    countInfo.textContent = `Mostrant ${items.length} de ${total}`;
    pageInfo.textContent = `Pàgina ${page} de ${pages}`;

    prevBtn.disabled = !(pag?.has_prev ?? page > 1);
    nextBtn.disabled = !(pag?.has_next ?? page < pages);

    // sync selects
    cursSelect.value = String(state.curs);
    langSelect.value = String(state.lang);
    statusSelect.value = String(state.status);
    orderSelect.value = state.order;
  }

  // filtros
  cursSelect.addEventListener('change', () => {
    state.curs = parseInt(cursSelect.value, 10) || 0;
    state.page = 1;
    load();
  });

  langSelect.addEventListener('change', () => {
    state.lang = parseInt(langSelect.value, 10) || 0;
    state.page = 1;
    load();
  });

  statusSelect.addEventListener('change', () => {
    state.status = String(statusSelect.value ?? '');
    state.page = 1;
    load();
  });

  orderSelect.addEventListener('change', () => {
    state.order = orderSelect.value === 'desc' ? 'desc' : 'asc';
    state.page = 1;
    load();
  });

  resetBtn.addEventListener('click', () => {
    state.page = 1;
    state.curs = 0;
    state.lang = 0;
    state.status = '';
    state.order = 'asc';

    cursSelect.value = '0';
    langSelect.value = '0';
    statusSelect.value = '';
    orderSelect.value = 'asc';

    load();
  });

  // paginación
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
