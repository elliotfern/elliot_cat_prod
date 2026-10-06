import { api } from '../../Infrastructure/Api/Client/ApiClient';

interface CursHistoria {
  id: string;
  curs: string;
  descripcio?: string;
}

interface ArticleHistoria {
  id: string;
  post_title: string;
  post_date: string;
  slug: string;
  ordre: number;
}

type CodiIdioma = 'ca' | 'es' | 'en' | 'fr' | 'it';

type ArticlesPerIdioma = Record<CodiIdioma, ArticleHistoria[]>;

interface CursHistoriaResponse {
  curs: CursHistoria;
  articles: ArticlesPerIdioma;
}

const IDIOMES: ReadonlyArray<{ codi: CodiIdioma; nom: string }> = [
  { codi: 'ca', nom: 'Català' },
  { codi: 'es', nom: 'Español' },
  { codi: 'en', nom: 'English' },
  { codi: 'fr', nom: 'Français' },
  { codi: 'it', nom: 'Italiano' },
];

export async function getCursHistoria(nameCourse: string): Promise<void> {
  try {
    const result = await api.get<CursHistoriaResponse>('historia/get/cursHistoria', { paramName: nameCourse });

    mostrarCurs(result);
  } catch (error: unknown) {
    console.error('Error fetching data:', error);
  }
}

function escapeHtml(text: string): string {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function renderArticles(articles: ArticleHistoria[]): string {
  if (articles.length === 0) {
    return `<p class="text-muted small mb-0">—</p>`;
  }

  const items = [...articles]
    .sort((a, b) => a.ordre - b.ordre)
    .map((article) => {
      const postLink = `/historia/article/${encodeURIComponent(article.slug)}`;

      return `
        <li class="list-group-item">
          <span class="text-muted me-2">${article.ordre}.</span>
          <a href="${postLink}" class="text-decoration-none">${escapeHtml(article.post_title)}</a>
        </li>
      `;
    })
    .join('');

  return `<ol class="list-group list-group-flush">${items}</ol>`;
}

function renderIdioma(codi: CodiIdioma, nom: string, articles: ArticleHistoria[]): string {
  return `
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">${nom}</span>
          <span class="badge text-bg-secondary">${articles.length}</span>
        </div>
        ${articles.length > 0 ? renderArticles(articles) : `<div class="card-body"><p class="text-muted small mb-0">Cap article en aquest idioma.</p></div>`}
      </div>
    </div>
  `;
}

function mostrarCurs(result: CursHistoriaResponse): void {
  const cursContainer = document.getElementById('curs');

  if (!cursContainer) {
    return;
  }

  const { curs, articles } = result;

  const totalArticles = IDIOMES.reduce((total, { codi }) => total + (articles[codi]?.length ?? 0), 0);

  const idiomesHtml = IDIOMES.map(({ codi, nom }) => renderIdioma(codi, nom, articles[codi] ?? [])).join('');

  const articlesContent =
    totalArticles > 0
      ? `<div class="row g-4">${idiomesHtml}</div>`
      : `
        <div class="alert alert-info">
          No hi ha cap article disponible per aquest curs.
        </div>
      `;

  cursContainer.innerHTML = `
    <div class="text-center mb-4">
      <h1>${escapeHtml(curs.curs)}</h1>

      ${curs.descripcio ? `<p class="small">${curs.descripcio}</p>` : ''}
    </div>

    <h2 class="h4 mb-3">Articles del curs</h2>

    ${articlesContent}
  `;
}
