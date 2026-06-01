// src/frontend/pages/gestio/historia/fitxaCursArticles.ts

import { DOMAIN_WEB } from '../../utils/urls';
import { TaulaDinamica } from '../../types/TaulaDinamica';

type BlogRef = {
  id: number;
  title: string;
  slug: string;
  status: string;
  editUrl: string;
};

type SlotItem = {
  slotId: number;
  curs: number;
  ordre: number;
  ca: BlogRef | null;
  es: BlogRef | null;
  en: BlogRef | null;
  fr: BlogRef | null;
  it: BlogRef | null;
};

type CursInfo = {
  id: number;
  nameCa?: string;
  nombreCurso?: string;
};

type ApiResponse = {
  status: string;
  message: string;
  errors: unknown[];
  data: {
    curs: CursInfo;
    items: SlotItem[];
  };
};

export async function taulaArticlesCurs(cursId: string): Promise<void> {
  const infoCurs = document.getElementById('infoCurs');
  const container = document.getElementById('llistatArticles');
  if (!infoCurs || !container) return;

  container.innerHTML = `<div class="alert alert-info">Carregant articles...</div>`;

  const url = `https://${window.location.host}/api/historia/get/cursArticles?cursId=${encodeURIComponent(cursId)}`;

  const res = await fetch(url, { credentials: 'include' });
  if (!res.ok) {
    container.innerHTML = `<div class="alert alert-danger">Error carregant el curs.</div>`;
    return;
  }

  const json = (await res.json()) as ApiResponse;
  const curs = json.data?.curs;
  const items = json.data?.items ?? [];

  // Nombre del curso
  const nom = (curs?.nameCa || curs?.nombreCurso || `Curs ID ${cursId}`) as string;

  // InfoCurs con un poco de Bootstrap 5
  infoCurs.innerHTML = `
    <div class="card shadow-sm mb-3">
      <div class="card-body py-3">
        <h2 class="h5 mb-0">${escapeHtml(nom)}</h2>
      </div>
    </div>
  `;

  const columns: TaulaDinamica<SlotItem>[] = [
    {
      header: 'Ordre',
      field: 'ordre',
      render: (value: unknown) => {
        const v = value === null || value === undefined || value === '' ? '—' : String(value);
        return `<span class="fw-semibold">${escapeHtml(v)}</span>`;
      },
    },
    { header: 'CAT', field: 'ca', render: (_: unknown, row: SlotItem) => renderLangCell(row.ca) },
    { header: 'ES', field: 'es', render: (_: unknown, row: SlotItem) => renderLangCell(row.es) },
    { header: 'EN', field: 'en', render: (_: unknown, row: SlotItem) => renderLangCell(row.en) },
    { header: 'FR', field: 'fr', render: (_: unknown, row: SlotItem) => renderLangCell(row.fr) },
    { header: 'IT', field: 'it', render: (_: unknown, row: SlotItem) => renderLangCell(row.it) },
    {
      header: 'Modifica',
      field: 'slotId',
      render: (_: unknown, row: SlotItem) => {
        const href = `${DOMAIN_WEB}/gestio/historia/modifica-curs-article/${row.slotId}`;
        return `
          <a href="${href}" class="btn btn-sm btn-primary">
            Modifica slot
          </a>
        `;
      },
    },
  ];

  renderTableLocal({
    containerId: 'llistatArticles',
    columns,
    rows: items.slice().sort((a, b) => a.ordre - b.ordre),
  });
}

// Render local (datos pre-cargados)
function renderTableLocal<T extends Record<string, unknown>>(opts: { containerId: string; columns: TaulaDinamica<T>[]; rows: T[] }) {
  const container = document.getElementById(opts.containerId);
  if (!container) return;

  const thead = `
    <thead class="table-light">
      <tr>
        ${opts.columns.map((c) => `<th scope="col" class="text-uppercase small">${escapeHtml(c.header)}</th>`).join('')}
      </tr>
    </thead>
  `;

  const tbody = `
    <tbody class="table-group-divider">
      ${
        opts.rows.length
          ? opts.rows
              .map((row) => {
                const tds = opts.columns
                  .map((col) => {
                    const value = (row as any)[col.field as any];
                    const html = col.render ? col.render(value, row as any) : escapeHtml(value);
                    return `<td class="py-2">${html}</td>`;
                  })
                  .join('');
                return `<tr>${tds}</tr>`;
              })
              .join('')
          : `<tr><td colspan="${opts.columns.length}" class="text-muted p-3">Sense articles.</td></tr>`
      }
    </tbody>
  `;

  container.innerHTML = `
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            ${thead}
            ${tbody}
          </table>
        </div>
      </div>
    </div>
  `;
}

function renderLangCell(ref: BlogRef | null): string {
  if (!ref) {
    return `<span class="text-muted">—</span>`;
  }

  // editor artículo db_blog
  const editHref = `${DOMAIN_WEB}/gestio/blog/modifica-article/${ref.id}`;

  // pequeño badge de status si quieres verlo
  const status = ref.status ? `<span class="badge text-bg-light ms-2">${escapeHtml(ref.status)}</span>` : '';

  return `
    <div class="d-flex flex-column gap-1">
      <div class="d-flex align-items-center">
        <div class="fw-semibold" style="line-height:1.2">
          ${escapeHtml(ref.title || '(sense títol)')}
        </div>
        ${status}
      </div>
      <div>
        <a href="${editHref}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
          Modifica article
        </a>
      </div>
    </div>
  `;
}

function escapeHtml(input: unknown): string {
  return String(input ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}
