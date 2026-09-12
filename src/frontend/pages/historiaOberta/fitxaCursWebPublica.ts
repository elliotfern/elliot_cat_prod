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
}

interface CursHistoriaResponse {
  curs: CursHistoria;
  articles: ArticleHistoria[];
}

export async function getCursHistoria(nameCourse: string): Promise<void> {
  try {
    const result = await api.get<CursHistoriaResponse>('historia/get/cursHistoria', { paramName: nameCourse });

    mostrarCurs(result);
  } catch (error: unknown) {
    console.error('Error fetching data:', error);
  }
}

function mostrarCurs(result: CursHistoriaResponse): void {
  const cursContainer = document.getElementById('curs');

  if (!cursContainer) {
    return;
  }

  cursContainer.innerHTML = '';

  const curs = result.curs;

  const articles = result.articles
    .map((article) => {
      const postLink = `/historia/article/${encodeURIComponent(article.slug)}`;

      return `
        <li class="mb-2">
          <a href="${postLink}" class="text-decoration-none">
            ${article.post_title}
          </a>
        </li>
      `;
    })
    .join('');

  const articlesContent =
    result.articles.length > 0
      ? `
        <ol>
          ${articles}
        </ol>
      `
      : `
        <div class="alert alert-info">
          No hi ha cap article disponible per aquest curs.
        </div>
      `;

  cursContainer.innerHTML = `
    <div class="text-center mb-4">
      <h1>${curs.curs}</h1>

      ${curs.descripcio ? `<p class="small">${curs.descripcio}</p>` : ''}
    </div>

    <h2 class="h4 mb-3">Articles del curs</h2>

    ${articlesContent}
  `;
}
