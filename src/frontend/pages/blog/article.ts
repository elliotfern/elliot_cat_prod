// src/frontend/pages/blog/articleView.ts
// Ruta pública: /blog/article/<slug>

type BlogArticleDetail = {
  id: number;
  post_title: string;
  post_excerpt?: string | null;
  post_content?: string | null; // HTML
  slug: string;
  post_date: string;
  post_modified?: string | null;
  tema_ca?: string | null;
  lang?: string | null;
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

async function fetchArticleBySlug(slug: string): Promise<BlogArticleDetail> {
  const usp = new URLSearchParams();
  usp.set('articleSlug', slug);

  const url = `https://${window.location.host}/api/blog/get/articleSlug?${usp.toString()}`;
  const r = await fetch(url, { credentials: 'include' });
  const json = await r.json();
  const data = (json?.data ?? json) as BlogArticleDetail;

  return data;
}

export async function renderBlogArticleView(slug: string): Promise<void> {
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
    const a = await fetchArticleBySlug(slug);

    const title = escapeHtml(a.post_title || '(Sense títol)');
    const excerpt = (a.post_excerpt ?? '').trim();
    const contentHtml = (a.post_content ?? '').trim(); // viene como HTML
    const cat = escapeHtml((a.tema_ca ?? 'Sense categoria') || 'Sense categoria');
    const dateLabel = escapeHtml(formatDateCa(a.post_date));

    // ⚠️ contentHtml se inserta como HTML (igual que haces en otras páginas).
    // Si el contenido puede venir de usuarios, habría que sanitizar en servidor o con una lib.
    container.innerHTML = `
      <div class="mb-3">
        <a class="text-decoration-none" href="/blog">← Tornar al blog</a>
      </div>

      <article class="card">
        <div class="card-body">
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
    `;

    const contentEl = container.querySelector<HTMLDivElement>('#blogContent');
    if (contentEl) {
      contentEl.innerHTML = contentHtml || `<div class="text-muted">Sense contingut.</div>`;
    }
  } catch (e) {
    container.innerHTML = `
      <div class="alert alert-danger mb-0">
        No s'ha pogut carregar l'article.
      </div>
    `;
  }
}
