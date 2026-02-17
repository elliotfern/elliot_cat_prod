// src/frontend/pages/gestio/historia/fitxaCursArticles.ts

import { DOMAIN_WEB } from '../../utils/urls';
import { TaulaDinamica } from '../../types/TaulaDinamica';

type BlogRef = {
  id: number;
  title: string;
  slug: string;
  status: string;
  editUrl: string; // viene del backend
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
  // según tu backend puede venir nameCa o nombreCurso, aquí soportamos ambos
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

  // 1) Fetch del endpoint
  container.innerHTML = `<div class="alert alert-info">Carregant articles...</div>`;

  const url = `https://${window.location.host}/api/historia-oberta/get/cursArticles?cursId=${encodeURIComponent(cursId)}`;

  const res = await fetch(url, { credentials: 'include' });
  if (!res.ok) {
    container.innerHTML = `<div class="alert alert-danger">Error carregant el curs.</div>`;
    return;
  }

  const json = (await res.json()) as ApiResponse;
  const curs = json.data?.curs;
  const items = json.data?.items ?? [];

  // 2) Pintar nombre del curso en #infoCurs
  const nom = (curs?.nameCa || curs?.nombreCurso || `Curs ID ${cursId}`) as string;
  infoCurs.innerHTML = `<h2 style="margin:0 0 10px 0">${escapeHtml(nom)}</h2>`;

  // 3) Definir columnas (mismo modelo: header/field/render)
  const columns: TaulaDinamica<SlotItem>[] = [
    {
      header: 'Ordre',
      field: 'ordre',
      render: (value: unknown) => {
        const v = value === null || value === undefined || value === '' ? '—' : String(value);
        return `<span class="text-muted">${escapeHtml(v)}</span>`;
      },
    },
    {
      header: 'CAT',
      field: 'ca',
      render: (_: unknown, row: SlotItem) => renderLangCell(row.ca),
    },
    {
      header: 'ES',
      field: 'es',
      render: (_: unknown, row: SlotItem) => renderLangCell(row.es),
    },
    {
      header: 'EN',
      field: 'en',
      render: (_: unknown, row: SlotItem) => renderLangCell(row.en),
    },
    {
      header: 'FR',
      field: 'fr',
      render: (_: unknown, row: SlotItem) => renderLangCell(row.fr),
    },
    {
      header: 'IT',
      field: 'it',
      render: (_: unknown, row: SlotItem) => renderLangCell(row.it),
    },
    {
      header: 'Modifica',
      field: 'slotId',
      render: (_: unknown, row: SlotItem) => {
        // De momento: solo “slotId” (POST lo haremos luego)
        return `<span class="text-muted">Slot ${row.slotId}</span>`;
      },
    },
  ];

  // 4) Render table “como renderDynamicTable”, pero con datos ya cargados
  renderTableLocal({
    containerId: 'llistatArticles',
    columns,
    rows: items.sort((a, b) => a.ordre - b.ordre),
  });
}

// ---------------------------------------------------------
// Render local (mismo concepto de renderDynamicTable, pero
// usando rows pre-cargadas porque tu endpoint no es array plano)
// ---------------------------------------------------------

function renderTableLocal<T extends Record<string, unknown>>(opts: { containerId: string; columns: TaulaDinamica<T>[]; rows: T[] }) {
  const container = document.getElementById(opts.containerId);
  if (!container) return;

  const thead = `
    <thead>
      <tr>
        ${opts.columns.map((c) => `<th>${escapeHtml(c.header)}</th>`).join('')}
      </tr>
    </thead>
  `;

  const tbody = `
    <tbody>
      ${
        opts.rows.length
          ? opts.rows
              .map((row) => {
                const tds = opts.columns
                  .map((col) => {
                    const value = (row as any)[col.field as any];
                    const html = col.render ? col.render(value, row as any) : escapeHtml(value);
                    return `<td>${html}</td>`;
                  })
                  .join('');
                return `<tr>${tds}</tr>`;
              })
              .join('')
          : `<tr><td colspan="${opts.columns.length}" class="text-muted">Sense articles.</td></tr>`
      }
    </tbody>
  `;

  container.innerHTML = `
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        ${thead}
        ${tbody}
      </table>
    </div>
  `;
}

function renderLangCell(ref: BlogRef | null): string {
  if (!ref) return `<span class="text-muted">—</span>`;

  // Tu editor: /gestio/blog/modifica-article/{id}
  const editHref = `${DOMAIN_WEB}/gestio/blog/modifica-article/${ref.id}`;

  return `
    <div>
      <div style="font-weight:600;font-size:14px;line-height:1.2">
        ${escapeHtml(ref.title || '(sense títol)')}
      </div>
      <a href="${editHref}" class="button btn-petit" style="display:inline-block;margin-top:6px">
        Modifica
      </a>
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
