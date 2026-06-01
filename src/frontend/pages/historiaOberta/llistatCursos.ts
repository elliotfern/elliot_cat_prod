// src/frontend/pages/gestio/historia/llistatCursos.ts

import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { formatData } from '../../utils/formataData';
import { DOMAIN_WEB } from '../../utils/urls';

type CursHistoriaApi = {
  id: number;
  ordre: number;
  nombreCurso: string;
  resumen: string;
  img: string;
  paramName: string;
  lastModified: string;
};

export async function taulaLlistatCursosHistoria(): Promise<void> {
  const columns: TaulaDinamica<CursHistoriaApi>[] = [
    {
      header: 'Ordre',
      field: 'ordre',
      render: (value: unknown) => {
        const v = value === null || value === undefined || value === '' ? '—' : String(value);
        return `<span class="text-muted">${escapeHtml(v)}</span>`;
      },
    },
    {
      header: 'Curs',
      field: 'nombreCurso',
      render: (_: unknown, row: CursHistoriaApi) => {
        // Link directo a la gestión de artículos del curso (ajusta la ruta si la decides distinta)
        return `
          <a href="${DOMAIN_WEB}/gestio/historia/fitxa-curs/${row.id}"}
          </a>
          ${row.nombreCurso ? `<div class="text-muted" style="font-size:12px">${escapeHtml(row.nombreCurso)}</div>` : ''}
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
            <button type="button" class="button btn-petit">Modifica curs</button>
          </a>
        `;
      },
    },
  ];

  renderDynamicTable({
    url: `https://${window.location.host}/api/historia/get/llistatCursos?langCurso=ca`,
    containerId: 'cursList',
    columns,
    filterKeys: ['nombreCurso'],
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
