// src/frontend/pages/gestio/historia/llistatCursos.ts

import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { formatData } from '../../utils/formataData';
import { DOMAIN_WEB } from '../../utils/urls';

type CursHistoriaApi = {
  id: string;
  ordre: number;
  curs: string;
  resum: string;
  img: string;
  slug: string;
  lastModified: string;
};

export async function taulaLlistatCursosHistoria(): Promise<void> {
  const columns: TaulaDinamica<CursHistoriaApi>[] = [
    {
      header: 'Ordre',
      field: 'ordre',
      render: (value: unknown) => {
        const v = value === null || value === undefined || value === '' ? '—' : String(value);
        return `${escapeHtml(v)}`;
      },
    },
    {
      header: 'Curs',
      field: 'curs',
      render: (_: unknown, row: CursHistoriaApi) => {
        return `
          <a href="${DOMAIN_WEB}/gestio/historia/fitxa-curs/${row.id}"}
          </a>
          ${row.curs ? `${escapeHtml(row.curs)}` : ''}
        `;
      },
    },
    {
      header: 'Actualitzat',
      field: 'lastModified',
      render: (value: unknown) => {
        const v = value ? String(value) : '—';
        return `<span class="text-muted">${formatData(v)}</span>`;
      },
    },
    {
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: CursHistoriaApi) => {
        return `


          <a href="${DOMAIN_WEB}/gestio/historia/modifica-curs/${row.id}">
            <button type="button" class="btn btn-warning btn-sm">Modifica curs</button>
          </a>
        `;
      },
    },
  ];

  renderDynamicTable({
    url: `historia/get/llistatCursos`,
    containerId: 'cursList',
    columns,
    filterKeys: ['curs'],
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
