// llistatArticles.ts
// Renderitza llistat d'articles amb buscador + filtres (any / categoria)
// Container esperat: <div id="articleList"></div>

type BlogArticle = {
  id: number;
  post_type: string;
  post_title: string;
  post_excerpt?: string | null;
  lang?: string | null;
  post_status?: string | null;
  slug: string;
  categoria?: number | string | null;
  post_date: string;      // "YYYY-MM-DD ..." o ISO
  post_modified?: string; // opcional
  tema_ca?: string | null; // nom categoria (CA)
};

type ApiResponse<T> =
  | { status: 'success' | 'ok'; data: T; message?: string }
  | { status: 'error'; message?: string; errors?: unknown; data?: T }
  | T; // fallback si l'API retorna l'array directament

function escapeHtml(input: unknown): string {
  return String(input ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function parseApiData(resp: ApiResponse<BlogArticle[]>): BlogArticle[] {
  if (Array.isArray(resp)) return resp;
  if (resp && Array.isArray((resp as any).data)) return (resp as any).data;
  return [];
}

function getYearFromDate(dateStr: string): number | null {
  if (!dateStr) return null;
  const m = String(dateStr).match(/^(\d{4})/);
  return m ? Number(m[1]) : null;
}

function formatDateCa(dateStr: string): string {
  // Intenta parsejar "YYYY-MM-DD ..." sense trencar.
  const iso = String(dateStr).trim().replace(' ', 'T');
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) {
    // fallback: retorna només YYYY-MM-DD si existeix
    const m = String(dateStr).match(/^(\d{4}-\d{2}-\d{2})/);
    return m ? m[1] : String(dateStr);
  }
  return d.toLocaleDateString('ca-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

function normalizeText(s: string): string {
  return (s ?? '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, ''); // treu accents
}

function buildArticleUrl(row: BlogArticle): string {
  // Ajusta aquí la ruta pública real del teu blog si és diferent
  // Ex: /blog/<slug> o /ca/blog/<slug>
  return `/blog/${encodeURIComponent(row.slug)}`;
}

export async function renderLlistatArticlesBlog(): Promise<void> {
  const container = document.getElementById('articleList');
  if (!container) return;

  container.innerHTML = `
    <div class="d-flex flex-column gap-3">
      <div class="card">
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-6">
              <label class="form-label mb-1" for="blogSearchInput">Cerca</label>
              <input id="blogSearchInput" type="search" class="form-control" placeholder="Cerca per títol..." />
            </div>
            <div class="col-12 col-lg-3">
              <label class="form-label mb-1" for="blogYearSelect">Any</label>
              <select id="blogYearSelect" class="form-select"></select>
            </div>
            <div class="col-12 col-lg-3">
              <label class="form-label mb-1" for="blogCatSelect">Categoria</label>
              <select id="blogCatSelect" class="form-select"></select>
            </div>
          </div>

          <div class="mt-3 d-flex flex-wrap gap-2" id="blogYearChips"></div>

          <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-muted" id="blogCountInfo">Carregant...</div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="blogResetBtn">
              Neteja filtres
            </button>
          </div>
        </div>
      </div>

      <div id="blogListWrap" class="d-flex flex-column gap-2"></div>
    </div>
  `;

  const searchInput = container.querySelector<HTMLInputElement>('#blogSearchInput')!;
  const yearSelect = container.querySelector<HTMLSelectElement>('#blogYearSelect')!;
  const catSelect = container.querySelector<HTMLSelectElement>('#blogCatSelect')!;
  const yearChips = container.querySelector<HTMLDivElement>('#blogYearChips')!;
  const listWrap = container.querySelector<HTMLDivElement>('#blogListWrap')!;
  const countInfo = container.querySelector<HTMLDivElement>('#blogCountInfo')!;
  const resetBtn = container.querySelector<HTMLButtonElement>('#blogResetBtn')!;

  // 1) Fetch
  const url = `https://${window.location.host}/api/blog/get/llistatArticles`;
  let all: BlogArticle[] = [];
  try {
    const r = await fetch(url, { credentials: 'include' });
    const json = (await r.json()) as ApiResponse<BlogArticle[]>;
    all = parseApiData(json);
  } catch (e) {
    listWrap.innerHTML = `
      <div class="alert alert-danger mb-0">
        No s'ha pogut carregar el llistat d'articles.
      </div>
    `;
    countInfo.textContent = 'Error de càrrega';
    return;
  }

  // 2) Normalitza + ordena (tu tens ORDER BY ASC, però aquí ho deixo en DESC per UX; canvia si vols)
  const rows = all
    .map((x) => ({
      ...x,
      _year: getYearFromDate(x.post_date) ?? 0,
      _cat: (x.tema_ca ?? '').trim() || 'Sense categoria',
      _title: (x.post_title ?? '').trim(),
      _dateLabel: formatDateCa(x.post_date),
    }))
    .sort((a, b) => (b.post_date > a.post_date ? 1 : -1)); // DESC per defecte

  // 3) Dades per filtres
  const years = Array.from(new Set(rows.map((r) => r._year).filter((y) => y > 0))).sort((a, b) => b - a);
  const cats = Array.from(new Set(rows.map((r) => r._cat))).sort((a, b) => a.localeCompare(b, 'ca'));

  // Estat
  let state = {
    q: '',
    year: 'all' as 'all' | string, // string = "2026" etc
    cat: 'all' as 'all' | string,
  };

  // 4) UI filtres
  yearSelect.innerHTML = [
    `<option value="all">Tots</option>`,
    ...years.map((y) => `<option value="${y}">${y}</option>`),
  ].join('');

  catSelect.innerHTML = [
    `<option value="all">Totes</option>`,
    ...cats.map((c) => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`),
  ].join('');

  // Chips per any (ràpids)
  const buildYearChips = () => {
    // Mostra alguns anys com a "píndoles" clicables (tots + fins a 10 anys)
    const max = 10;
    const chipYears = years.slice(0, max);
    yearChips.innerHTML = [
      `<button type="button" class="btn btn-sm ${state.year === 'all' ? 'btn-primary' : 'btn-outline-primary'}" data-year="all">Tots</button>`,
      ...chipYears.map(
        (y) =>
          `<button type="button" class="btn btn-sm ${
            state.year === String(y) ? 'btn-primary' : 'btn-outline-primary'
          }" data-year="${y}">${y}</button>`
      ),
    ].join('');
  };

  const applyFilters = () => {
    const qn = normalizeText(state.q);
    const filtered = rows.filter((r) => {
      const okYear = state.year === 'all' ? true : String(r._year) === state.year;
      const okCat = state.cat === 'all' ? true : r._cat === state.cat;
      const okQ = !qn ? true : normalizeText(r._title).includes(qn);
      return okYear && okCat && okQ;
    });

    // Agrupa per any (per header)
    const grouped = new Map<number, typeof filtered>();
    for (const r of filtered) {
      const y = r._year || 0;
      if (!grouped.has(y)) grouped.set(y, []);
      grouped.get(y)!.push(r);
    }
    const groupKeys = Array.from(grouped.keys()).sort((a, b) => b - a);

    // Render
    if (filtered.length === 0) {
      listWrap.innerHTML = `<div class="alert alert-secondary mb-0">No hi ha resultats amb aquests filtres.</div>`;
      countInfo.textContent = `0 resultats`;
      buildYearChips();
      return;
    }

    const html: string[] = [];
    for (const year of groupKeys) {
      const items = grouped.get(year)!;

      html.push(`
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <strong>${year || 'Sense any'}</strong>
            <span class="badge text-bg-secondary">${items.length}</span>
          </div>
          <div class="list-group list-group-flush">
            ${items
              .map((row) => {
                const href = buildArticleUrl(row);
                const title = escapeHtml(row._title || '(Sense títol)');
                const cat = escapeHtml(row._cat);
                const dateLabel = escapeHtml(row._dateLabel);

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
              .join('')}
          </div>
        </div>
      `);
    }

    listWrap.innerHTML = html.join('');
    countInfo.textContent = `${filtered.length} resultats`;
    buildYearChips();
  };

  // 5) Events
  searchInput.addEventListener('input', () => {
    state.q = searchInput.value.trim();
    applyFilters();
  });

  yearSelect.addEventListener('change', () => {
    state.year = yearSelect.value === 'all' ? 'all' : yearSelect.value;
    applyFilters();
  });

  catSelect.addEventListener('change', () => {
    state.cat = catSelect.value === 'all' ? 'all' : catSelect.value;
    applyFilters();
  });

  yearChips.addEventListener('click', (ev) => {
    const btn = (ev.target as HTMLElement).closest<HTMLButtonElement>('button[data-year]');
    if (!btn) return;
    const y = btn.dataset.year!;
    state.year = y === 'all' ? 'all' : y;
    yearSelect.value = state.year;
    applyFilters();
  });

  resetBtn.addEventListener('click', () => {
    state = { q: '', year: 'all', cat: 'all' };
    searchInput.value = '';
    yearSelect.value = 'all';
    catSelect.value = 'all';
    applyFilters();
  });

  // 6) Render inicial
  applyFilters();
}
