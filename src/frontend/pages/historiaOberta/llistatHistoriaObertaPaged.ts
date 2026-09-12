import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { DOMAIN_WEB } from '../../utils/urls';
import { langIdToCode } from '../../utils/locales/getLangPrefix';

interface HistoriaObertaRow {
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
}

export async function renderHistoriaObertaList(): Promise<void> {
  const container = document.getElementById('articleList');

  if (!container) {
    return;
  }

  container.innerHTML = `
    <div class="alert alert-info">
      Carregant articles...
    </div>
  `;

  try {
    const columns: TaulaDinamica<HistoriaObertaRow>[] = [
      {
        header: 'Article',
        field: 'post_title',
        render: (value: unknown) => `<span class="fw-semibold">${escapeHtml(value || '(Sense títol)')}</span>`,
      },
      {
        header: 'Curs',
        field: 'course_name',
        render: (value: unknown) => escapeHtml(value || '—'),
      },
      {
        header: 'Idioma',
        field: 'lang',
        render: (value: unknown) => `<span class="badge text-bg-light border">${escapeHtml(langIdToCode(Number(value)))}</span>`,
      },
      {
        header: 'Estat',
        field: 'post_status',
        render: (value: unknown) => renderStatus(typeof value === 'string' ? value : null),
      },
      {
        header: 'Data',
        field: 'post_date',
        render: (value: unknown) => escapeHtml(formatDateCa(String(value ?? ''))),
      },
      {
        header: 'Ordre',
        field: 'article_ordre',
        render: (value: unknown) => escapeHtml(value ?? '—'),
      },
      {
        header: 'Accions',
        field: 'blog_id',
        render: (_value: unknown, row: HistoriaObertaRow) => {
          const publicUrl = buildPublicUrl(row.slug, row.lang);
          const editUrl = buildEditUrl(row.blog_id);

          return `
            <div class="d-flex gap-2 flex-wrap">
              <a
                href="${escapeHtml(publicUrl)}"
                class="btn btn-sm btn-outline-secondary"
                target="_blank"
                rel="noopener noreferrer"
              >
                Veure
              </a>

              <a
                href="${escapeHtml(editUrl)}"
                class="btn btn-sm btn-outline-primary"
              >
                Edita
              </a>
            </div>
          `;
        },
      },
    ];

    renderDynamicTable({
      url: `blog/get/llistatHistoriaOberta`,
      containerId: 'articleList',
      columns,
      filterKeys: ['course_name'],
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

function buildEditUrl(blogId: number): string {
  return `${DOMAIN_WEB}/gestio/blog/modifica-article/${encodeURIComponent(String(blogId))}`;
}

function buildPublicUrl(slug: string, langId: number): string {
  const code = langIdToCode(langId);

  return `/${code}/historia/article/${encodeURIComponent(slug)}`;
}

function renderStatus(status?: string | null): string {
  if (!status) {
    return '<span class="text-muted">—</span>';
  }

  return `<span class="badge text-bg-light border">${escapeHtml(status)}</span>`;
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
    month: '2-digit',
    day: '2-digit',
  });
}

function escapeHtml(input: unknown): string {
  return String(input ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}
