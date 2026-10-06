import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { decorateLinksInHtml } from '../../utils/linksExterns';

interface BlogArticleDetail {
  id: string;
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

interface CursArticleItem {
  id: string;
  post_title: string;
  post_date: string;
  slug: string;
  ordre: number;
}

interface CursArticleResponse {
  curs: { id: string; curs: string; slug: string } | null;
  articles: CursArticleItem[];
}

interface IdiomaArticle {
  codi: 'ca' | 'es' | 'en' | 'fr' | 'it';
  slug: string;
  post_title: string;
}

interface IdiomesArticleResponse {
  idiomes: IdiomaArticle[];
}

const NOMS_IDIOMA: Record<IdiomaArticle['codi'], string> = {
  ca: 'Català',
  es: 'Español',
  en: 'English',
  fr: 'Français',
  it: 'Italiano',
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

async function renderCursArticles(currentSlug: string): Promise<void> {
  const target = document.getElementById('cursArticles');

  if (!target) {
    return;
  }

  try {
    const result = await api.get<CursArticleResponse>('historia/get/cursArticle', {
      articleSlug: currentSlug,
    });

    if (!result.curs || result.articles.length === 0) {
      return;
    }

    const cursHref = `/historia/curs/${encodeURIComponent(result.curs.slug)}`;

    const items = [...result.articles]
      .sort((a, b) => a.ordre - b.ordre)
      .map((article) => {
        if (article.slug === currentSlug) {
          return `
            <li class="list-group-item bg-light fw-semibold" aria-current="true">
              <span class="text-muted me-2">${article.ordre}.</span>${escapeHtml(article.post_title)}
            </li>
          `;
        }

        const href = `/historia/article/${encodeURIComponent(article.slug)}`;

        return `
          <li class="list-group-item">
            <span class="text-muted me-2">${article.ordre}.</span>
            <a href="${href}" class="text-decoration-none">${escapeHtml(article.post_title)}</a>
          </li>
        `;
      })
      .join('');

    target.innerHTML = `
      <div class="card shadow-sm">
        <div class="card-header">
          <span class="text-muted">Curs:</span>
          <a href="${cursHref}" class="fw-semibold text-decoration-none">${escapeHtml(result.curs.curs)}</a>
        </div>
        <div class="card-body pb-2">
          <h2 class="h6 mb-0">Articles disponibles:</h2>
        </div>
        <ul class="list-group list-group-flush">${items}</ul>
      </div>
    `;
  } catch (error: unknown) {
    // La targeta del curs és complementària: si falla, l'article es continua veient
    console.error("Error carregant el curs de l'article:", error);
  }
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
      <div id="idiomesArticle" class="mb-3"></div>

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

      <div id="cursArticles" class="mt-4"></div>
    `;

    const contentElement = container.querySelector<HTMLDivElement>('#blogContent');

    if (contentElement) {
      contentElement.innerHTML = contentHtml || '<div class="text-muted">Sense contingut.</div>';
    }

    void renderIdiomesArticle(slug);
    void renderCursArticles(slug);
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

async function renderIdiomesArticle(currentSlug: string): Promise<void> {
  const target = document.getElementById('idiomesArticle');

  if (!target) {
    return;
  }

  try {
    const result = await api.get<IdiomesArticleResponse>('historia/get/idiomesArticle', {
      articleSlug: currentSlug,
    });

    // Sense traduccions (només hi ha l'article actual): no cal pintar res
    if (result.idiomes.length < 2) {
      return;
    }

    const links = result.idiomes
      .map((idioma) => {
        const nom = escapeHtml(NOMS_IDIOMA[idioma.codi]);

        if (idioma.slug === currentSlug) {
          return `<span class="btn btn-sm btn-primary disabled" aria-current="true">${nom}</span>`;
        }

        const href = `/historia/article/${encodeURIComponent(idioma.slug)}`;

        return `
          <a href="${href}"
             class="btn btn-sm btn-outline-primary"
             lang="${idioma.codi}"
             hreflang="${idioma.codi}"
             title="${escapeHtml(idioma.post_title)}">
            ${nom}
          </a>
        `;
      })
      .join('');

    target.innerHTML = `
      <div class="card shadow-sm">
        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
          <span class="text-muted small me-1">Article disponible en:</span>
          ${links}
        </div>
      </div>
    `;
  } catch (error: unknown) {
    console.error("Error carregant els idiomes de l'article:", error);
  }
}
