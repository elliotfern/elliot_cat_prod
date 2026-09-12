import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { DOMAIN_WEB } from '../../utils/urls';

interface ArticleSlot {
  id: number;
  title: string;
  slug: string;
  status: string;
}

interface HistoriaObertaSlot {
  slotId: string;
  cursId: string;
  ordre: number;
  ca: ArticleSlot | null;
  es: ArticleSlot | null;
  en: ArticleSlot | null;
  fr: ArticleSlot | null;
  it: ArticleSlot | null;
}

export async function renderCursosArticlesList(): Promise<void> {
  const container = document.getElementById('CursosArticleList');

  if (!container) {
    return;
  }

  container.innerHTML = `
    <div class="alert alert-info">
      Carregant articles...
    </div>
  `;

  try {
    const columns: TaulaDinamica<HistoriaObertaSlot>[] = [
      {
        header: 'Català',
        field: 'ca',
        render: (value: unknown) => renderArticle(value),
      },
      {
        header: 'Español',
        field: 'es',
        render: (value: unknown) => renderArticle(value),
      },
      {
        header: 'English',
        field: 'en',
        render: (value: unknown) => renderArticle(value),
      },
      {
        header: 'Français',
        field: 'fr',
        render: (value: unknown) => renderArticle(value),
      },
      {
        header: 'Italiano',
        field: 'it',
        render: (value: unknown) => renderArticle(value),
      },
      {
        header: 'Ordre',
        field: 'ordre',
      },
      {
        header: 'Accions',
        field: 'slotId',
        render: (_value: unknown, row: HistoriaObertaSlot) => {
          const editUrl = buildEditUrl(row.slotId);

          return `
            <a
              href="${escapeHtml(editUrl)}"
              class="btn btn-sm btn-outline-primary"
            >
              Modificar
            </a>
          `;
        },
      },
    ];

    renderDynamicTable({
      url: 'historia/get/llistatHistoriaObertaSlots',
      containerId: 'CursosArticleList',
      columns,
    });
  } catch (error: unknown) {
    console.error('Error carregant articles:', error);

    container.innerHTML = `
      <div class="alert alert-danger">
        Error carregant els articles.
      </div>
    `;
  }
}

function renderArticle(value: unknown): string {
  if (!isArticleSlot(value)) {
    return '<span class="text-muted">—</span>';
  }

  return `
    <div class="fw-semibold">
      ${escapeHtml(value.title || '(Sense títol)')}
    </div>
    <small class="text-muted">
      ${escapeHtml(value.status)}
    </small>
  `;
}

function isArticleSlot(value: unknown): value is ArticleSlot {
  if (!value || typeof value !== 'object') {
    return false;
  }

  const article = value as Record<string, unknown>;

  return typeof article.id === 'number' && typeof article.title === 'string' && typeof article.slug === 'string' && typeof article.status === 'string';
}

function buildEditUrl(slotId: string): string {
  return `${DOMAIN_WEB}/gestio/historia/modifica-curs-article/${encodeURIComponent(slotId)}`;
}

function escapeHtml(value: unknown): string {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
