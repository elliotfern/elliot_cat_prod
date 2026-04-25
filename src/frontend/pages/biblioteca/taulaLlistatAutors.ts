import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
// import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Persona } from '../../types/Persona';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { getLangPrefix } from '../../utils/locales/getLangPrefix';
import { API_BASE, DOMAIN_WEB, INTRANET_WEB } from '../../utils/urls';

export async function taulaLlistatAutors() {
  const isAdmin = await getIsAdmin(); // Comprovar si és admin

  // ✅ Admin => /gestio ; Públic => /{lang}
  const basePrefix = isAdmin ? 'gestio' : getLangPrefix();

  const columns: TaulaDinamica<Persona>[] = [
    {
      header: 'Autor/a',
      field: 'id',
      render: (_: unknown, row: Persona) => `<a href="${DOMAIN_WEB}/${basePrefix}/biblioteca/fitxa-autor/${encodeURIComponent(row.slug)}">
          ${row.cognoms} ${row.cognoms}
        </a>`,
    },
    { header: 'País', field: 'pais_ca' },
    { header: 'Professió', field: 'grup' },
    {
      header: 'Dates',
      field: 'any_defuncio',
      render: (_: unknown, row: Persona) => {
        return `${!row.any_defuncio ? row.any_naixement : `${row.any_naixement} - ${row.any_defuncio}`}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Persona) => `
        <a href="${INTRANET_WEB}/base-dades-persones/modifica-persona/${row.slug}">
           <button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `${API_BASE}/biblioteca/get/?type=totsAutors`,
    containerId: 'taulaLlistatAutors',
    columns,
    filterKeys: ['cognoms'],
    filterByField: 'grup',

    // ✅ NOMÉS aquí
    filterSplitBy: { grup: ',' },
    filterSplitTrim: true,
  });
}
