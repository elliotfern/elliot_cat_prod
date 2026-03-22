import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Llibre } from '../../types/Llibre';
import { API_BASE, DOMAIN_WEB } from '../../utils/urls';
import { buildFrontUrl, getLangPrefix } from '../../utils/locales/getLangPrefix';

function escapeHtml(s: string): string {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

export async function taulaLlistatGrups() {
  const isAdmin = await getIsAdmin();
  const basePrefix = isAdmin ? 'gestio' : getLangPrefix();

  const columns: TaulaDinamica<Llibre>[] = [
    {
      header: 'Col·lecció',
      field: 'nom',
      render: (_: unknown, row: Llibre) => `<a href="${buildFrontUrl(`biblioteca/fitxa-grup/${row.id}`)}">${escapeHtml(row.nom)}</a>`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Llibre) => `
        <a href="${DOMAIN_WEB}/gestio/biblioteca/modifica-grup/${encodeURIComponent(row.id)}">
          <button type="button" class="button btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable<Llibre>({
    url: `${API_BASE}/biblioteca/get/?type=grupLlibre`,
    containerId: 'taulaLlistatGrups',
    columns,
    filterKeys: ['nom'],
    filterByField: 'nom',
  });
}
