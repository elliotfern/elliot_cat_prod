import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Llibre } from '../../types/Llibre';
import { API_BASE, DOMAIN_WEB } from '../../utils/urls';
import { buildFrontUrl, getLangPrefix } from '../../utils/locales/getLangPrefix';

function escapeHtml(s: string): string {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function renderAutorsCell(row: Llibre, basePrefix: string): string {
  const base = `${DOMAIN_WEB}/${basePrefix}/biblioteca/fitxa-autor/`;
  const autors = Array.isArray((row as any).autors) ? (row as any).autors : [];
  if (autors.length === 0) return '';

  return autors
    .filter((a: any) => a && typeof a.slug === 'string' && a.slug.length > 0)
    .map((a: any) => {
      const fullName = [a.nom, a.cognoms].filter(Boolean).join(' ').trim();
      const label = escapeHtml(fullName || a.slug);
      const href = `${base}${encodeURIComponent(a.slug)}`;
      return `<a href="${href}">${label}</a>`;
    })
    .join(' / ');
}

export async function taulaLlistatLlibres() {
  const isAdmin = await getIsAdmin();
  const basePrefix = isAdmin ? 'gestio' : getLangPrefix();

  const columns: TaulaDinamica<Llibre>[] = [
    {
      header: 'Llibre',
      field: 'titol',
      render: (_: unknown, row: Llibre) => `<a href="${buildFrontUrl(`/biblioteca/fitxa-llibre/${row.slug}`)}">${escapeHtml(row.titol)}</a>`,
    },
    {
      header: 'Autor/a',
      field: 'autors' as any, // por si tu generic exige field existente
      render: (_: unknown, row: Llibre) => renderAutorsCell(row, basePrefix),
    },
    {
      header: 'Gènere',
      field: 'nomGenCat',
      render: (_: unknown, row: Llibre) => {
        const nomGenCat = row.nomGenCat ?? '';
        const sub = (row as any).sub_tema_ca ?? (row as any).sub_genere_cat ?? '';
        // Si ya tienes sub_tema_ca (nuevo endpoint) mejor
        const text = sub ? `${nomGenCat} (${sub})` : `${nomGenCat}`;
        return escapeHtml(text);
      },
    },
    {
      header: 'Any',
      field: 'any',
      render: (_: unknown, row: Llibre) => `${row.any ?? ''}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Llibre) => `
        <a href="${DOMAIN_WEB}/gestio/biblioteca/modifica-llibre/${encodeURIComponent(row.slug)}">
          <button type="button" class="button btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable<Llibre>({
    url: `${API_BASE}/biblioteca/get/?type=totsLlibres`,
    containerId: 'taulaLlistatLlibres',
    columns,
    filterKeys: ['titol'],
    filterByField: 'nomGenCat',
  });
}
