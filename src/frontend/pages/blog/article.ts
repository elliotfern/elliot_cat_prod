// src/frontend/pages/blog/articleView.ts
// Ruta pública: /blog/article/<slug>

import { getIsAdmin } from '../../services/auth/isAdmin';

type ArticleScope = 'blog' | 'historia';

type LangCode = 'ca' | 'es' | 'en' | 'fr' | 'it';

function getUrlLangCode(): LangCode | null {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const first = String(parts[0] ?? '').toLowerCase();
  if (first in LANG_MAP) return first as LangCode;
  return null;
}

const LANG_MAP: Record<LangCode, number> = {
  ca: 1,
  es: 2,
  en: 3,
  fr: 4,
  it: 7,
};

function langCodeToId(code: LangCode): number {
  return LANG_MAP[code];
}

const LANG_ID_TO_CODE: Record<number, LangCode> = Object.fromEntries(Object.entries(LANG_MAP).map(([code, id]) => [id, code as LangCode])) as Record<number, LangCode>;

function parseLangId(v: unknown): number | null {
  const n = Number(String(v ?? '').trim());
  return Number.isFinite(n) && n > 0 ? n : null;
}

type BlogArticleDetail = {
  id: number;
  post_title: string;
  post_excerpt?: string | null;
  post_content?: string | null; // HTML
  slug: string;
  post_date: string;
  post_modified?: string | null;
  tema_ca?: string | null;
  lang?: number | null;
  post_status?: string | null;
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
  return d.toLocaleDateString('ca-ES', { year: 'numeric', month: 'long', day: '2-digit' });
}

async function fetchArticleBySlug(slug: string, tipus: ArticleScope): Promise<BlogArticleDetail> {
  const usp = new URLSearchParams();
  usp.set('articleSlug', slug);
  usp.set('scope', tipus);

  const url = `https://${window.location.host}/api/blog/get/articleSlug?${usp.toString()}`;
  const r = await fetch(url, { credentials: 'include' });

  // si el backend devuelve 404, lo tratamos como error
  if (!r.ok) {
    throw new Error(`HTTP_${r.status}`);
  }

  const json = await r.json();
  const data = (json?.data ?? json) as BlogArticleDetail;

  return data;
}

export async function renderBlogArticleView(slug: string, tipus: ArticleScope): Promise<void> {
  const container = document.getElementById('articleView');
  if (!container) return;

  if (!slug) {
    container.innerHTML = `<div class="alert alert-danger mb-0">Slug invàlid.</div>`;
    return;
  }

  container.innerHTML = `
    <div class="d-flex align-items-center gap-2 text-muted">
      <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
      <div>Carregant article…</div>
    </div>
  `;

  try {
    // 🔹 Paralelizamos carga de artículo + admin
    const [a, isAdmin] = await Promise.all([fetchArticleBySlug(slug, tipus), getIsAdmin()]);

    // ✅ En HISTORIA: redirigir si idioma no coincide
    if (tipus === 'historia') {
      const urlCode = getUrlLangCode();
      const articleLangId = Number(a.lang);

      if (urlCode && Number.isFinite(articleLangId)) {
        const correctCode = LANG_ID_TO_CODE[articleLangId];

        if (correctCode && correctCode !== urlCode) {
          // reconstruimos la URL con el idioma correcto
          const parts = window.location.pathname.split('/').filter(Boolean);

          // parts[0] es el idioma actual
          parts[0] = correctCode;

          const newPath = '/' + parts.join('/');

          window.location.replace(newPath);
          return; // muy importante
        }
      }
    }

    const title = escapeHtml(a.post_title || '(Sense títol)');
    const excerpt = (a.post_excerpt ?? '').trim();
    const contentHtml = (a.post_content ?? '').trim(); // viene como HTML
    const cat = escapeHtml((a.tema_ca ?? 'Sense categoria') || 'Sense categoria');
    const dateLabel = escapeHtml(formatDateCa(a.post_date));

    const editButton = isAdmin
      ? `
        <div class="mb-3 text-end">
          <a href="/gestio/blog/modifica-article/${a.id}" 
             class="btn btn-sm btn-outline-primary">
             ✏️ Modificar article
          </a>
        </div>
      `
      : '';

    // ⚠️ contentHtml se inserta como HTML (igual que haces en otras páginas).
    // Si el contenido puede venir de usuarios, habría que sanitizar en servidor o con una lib.
    container.innerHTML = `
      <article class="card">
        <div class="card-body">

         ${editButton}
         
          <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge text-bg-light border">${cat}</span>
            <span class="text-muted small">${dateLabel}</span>
          </div>

          <h1 class="h3 mb-3">${title}</h1>

          ${excerpt ? `<p class="lead">${escapeHtml(excerpt)}</p>` : ''}

          <hr class="my-4"/>

          <div class="blog-content" id="blogContent"></div>
        </div>
      </article>

      <div class="mb-3">
        <a class="text-decoration-none" href="/blog">← Tornar al blog</a>
      </div>
    `;

    const contentEl = container.querySelector<HTMLDivElement>('#blogContent');
    if (contentEl) {
      contentEl.innerHTML = contentHtml || `<div class="text-muted">Sense contingut.</div>`;
    }
  } catch (e) {
    let msg = `No s'ha pogut carregar l'article.`;

    if (e instanceof Error) {
      if (e.message === 'HTTP_404') msg = `Article no trobat.`;
      if (e.message === 'LANG_MISMATCH') msg = `Aquest article no està disponible en aquest idioma.`;
    }

    container.innerHTML = `
      <div class="alert alert-danger mb-0">
        ${escapeHtml(msg)}
      </div>
    `;
  }
}
