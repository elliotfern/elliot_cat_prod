import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { decorateLinksInHtml } from '../../utils/linksExterns';

interface BlogArticleDetail {
  id: number;
  post_title: string;
  post_excerpt?: string | null;
  post_content?: string | null;
  slug: string;
  post_date: string;
  post_modified?: string | null;
  tema?: string | null;
  lang?: number | null;
  post_status?: string | null;
}

function escapeHtml(input: unknown): string {
  return String(input ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function formatDateCa(dateStr: string): string {
  const iso = dateStr.trim().replace(' ', 'T');
  const date = new Date(iso);

  if (Number.isNaN(date.getTime())) {
    const match = dateStr.match(/^(\d{4}-\d{2}-\d{2})/);

    return match ? match[1] : dateStr;
  }

  return date.toLocaleDateString('ca-ES', {
    year: 'numeric',
    month: 'long',
    day: '2-digit',
  });
}

async function fetchArticleBySlug(slug: string): Promise<BlogArticleDetail> {
  return api.get<BlogArticleDetail>('historia/get/articleSlug', {
    articleSlug: slug,
  });
}

export async function renderArticle(slug: string): Promise<void> {
  const container = document.getElementById('articleView');

  if (!container) {
    return;
  }

  if (!slug) {
    container.innerHTML = `
      <div class="alert alert-danger mb-0">
        Slug invàlid.
      </div>
    `;

    return;
  }

  container.innerHTML = `
    <div class="d-flex align-items-center gap-2 text-muted">
      <div
        class="spinner-border spinner-border-sm"
        role="status"
        aria-hidden="true"
      ></div>
      <div>Carregant article…</div>
    </div>
  `;

  try {
    const article = await fetchArticleBySlug(slug);

    const title = escapeHtml(article.post_title || '(Sense títol)');
    const excerpt = (article.post_excerpt ?? '').trim();
    const contentHtml = decorateLinksInHtml(article.post_content ?? '').trim();

    const category = escapeHtml((article.tema ?? 'Sense categoria') || 'Sense categoria');

    const dateLabel = escapeHtml(formatDateCa(article.post_date));

    container.innerHTML = `
      <article class="card">
        <div class="card-body">

          <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="badge text-bg-light border">
              ${category}
            </span>

            <span class="text-muted small">
              ${dateLabel}
            </span>
          </div>

          <h1 class="h3 mb-3">
            ${title}
          </h1>

          ${excerpt ? `<p class="lead">${escapeHtml(excerpt)}</p>` : ''}

          <hr class="my-4"/>

          <div class="blog-content" id="blogContent"></div>
        </div>
      </article>
    `;

    const contentElement = container.querySelector<HTMLDivElement>('#blogContent');

    if (contentElement) {
      contentElement.innerHTML = contentHtml || '<div class="text-muted">Sense contingut.</div>';
    }
  } catch (error: unknown) {
    let message = `No s'ha pogut carregar l'article.`;

    if (error instanceof Error && error.message === 'HTTP_404') {
      message = 'Article no trobat.';
    }

    container.innerHTML = `
      <div class="alert alert-danger mb-0">
        ${escapeHtml(message)}
      </div>
    `;
  }
}
