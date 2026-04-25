import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Persona } from '../../types/Persona';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatPersones() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Persona>[] = [
    {
      header: 'Nom i cognoms',
      field: 'nom',
      render: (_: unknown, row: Persona) => {
        return `<a id="${row.id}" title="${row.nom}" 
               href="https://${window.location.hostname}/gestio/base-dades-persones/fitxa-persona/${row.slug}">
               ${row.nom}  ${row.cognoms}
            </a>`;
      },
    },

    { header: 'País', field: 'pais_ca' },

    {
      header: 'Grup',
      field: 'grup',
      render: (_: unknown, row: Persona) => (row.grup ?? []).join(', '),
    },

    {
      header: 'Anys',
      field: 'any_naixement',
      render: (_: unknown, row: Persona) => {
        const born = row.any_naixement ?? '';
        const died = row.any_defuncio;

        const diedText = died === null || died === 0 ? '' : ` - ${died}`;

        return `${born}${diedText}`;
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Persona) =>
        `<a id="${row.id}" title="Modifica" href="https://${window.location.hostname}/gestio/base-dades-persones/modifica-persona/${row.slug}">
          <button type="button" class="button btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `https://elliot.cat/api/persones/get/llistatPersones`,
    containerId: 'taulaLlistatPersones',
    columns,
    filterKeys: ['cognoms'],

    // 👇 IMPORTANTE: ahora filtramos por string seguro
    filterByField: 'grup',
  });
}
