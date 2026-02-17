// src/frontend/pages/blog/articleView.ts
// Ruta pública: /{lang}/blog/article/<slug>  i  /{lang}/historia/article/<slug>

import { getIsAdmin } from '../../services/auth/isAdmin';
import { decorateLinksInHtml } from '../../utils/linksExterns';
import { getUrlLangCode, LANG_ID_TO_CODE, isInGestio } from '../../utils/locales/getLangPrefix';

type ArticleScope = 'blog' | 'historia';

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

  if (!r.ok) throw new Error(`HTTP_${r.status}`);

  const json = await r.json();
  const data = (json?.data ?? json) as BlogArticleDetail;
  return data;
}

/**
 * Redirige a la misma ruta pero con el prefijo de idioma correcto según a.lang.
 * Mantiene querystring y hash.
 * Devuelve true si ha redirigido.
 */
function redirectIfLangMismatch(articleLangId: number | null | undefined): boolean {
  // 🚫 Nunca redirigir en la intranet
  if (isInGestio()) return false;

  const urlCode = getUrlLangCode() ?? 'ca';
  const n = Number(articleLangId);

  if (!Number.isFinite(n)) return false;

  const correctCode = LANG_ID_TO_CODE[n];
  if (!correctCode || correctCode === urlCode) return false;

  const parts = window.location.pathname.split('/').filter(Boolean);

  const hasLangPrefix = !!getUrlLangCode();
  const rest = hasLangPrefix ? parts.slice(1) : parts;

  const newPath = `/${correctCode}/${rest.join('/')}`;
  const newUrl = `${window.location.origin}${newPath}${window.location.search}${window.location.hash}`;

  window.location.replace(newUrl);
  return true;
}

export async function renderBlogArticleView(slug: string, tipus: ArticleScope): Promise<void> {
  const container = document.getElementById('articleView');
  if (!container) return;

  const langCode = getUrlLangCode() ?? 'ca';
  const backSection = tipus === 'historia' ? 'historia' : 'blog';
  const backHref = `/${langCode}/${backSection}`;
  const backLabel = tipus === 'historia' ? '← Tornar a la secció Història' : '← Tornar al blog';

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
    const [a, isAdmin] = await Promise.all([fetchArticleBySlug(slug, tipus), getIsAdmin()]);

    // ✅ Bloqueo por idioma para TODO: blog + historia
    // (si el artículo es 'ca' y estás en /en/... -> te manda a /ca/...)
    if (redirectIfLangMismatch(a.lang)) return;

    const title = escapeHtml(a.post_title || '(Sense títol)');
    const excerpt = (a.post_excerpt ?? '').trim();
    const contentHtml = decorateLinksInHtml(a.post_content ?? '').trim();
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
        <a class="text-decoration-none" href="${backHref}">
          ${backLabel}
        </a>
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
    }

    container.innerHTML = `
      <div class="alert alert-danger mb-0">
        ${escapeHtml(msg)}
      </div>
    `;
  }
}
