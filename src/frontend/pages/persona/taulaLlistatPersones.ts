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

  let gestioUrl = '';

  if (isAdmin) {
    gestioUrl = '/gestio';
  }

  const columns: TaulaDinamica<Persona>[] = [
    {
      header: 'Nom i cognoms',
      field: 'nom',
      render: (_: unknown, row: Persona) => {
        return `<a id="${row.id}" title="${row.nomComplet}" 
               href="https://${window.location.hostname}${gestioUrl}/base-dades-persones/fitxa-persona/${row.slug}">
               ${row.nomComplet}
            </a>`;
      },
    },

    { header: 'País', field: 'paisAutor' },

    {
      header: 'Grup',
      field: 'grup',
      render: (_: unknown, row: Persona) => {
        const grups = Array.isArray(row.grup) ? row.grup : [];
        return grups.join(', ');
      },
    },

    {
      header: 'Anys',
      field: 'yearBorn',
      render: (_: unknown, row: Persona) => `${row.anys}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Persona) =>
        `<a id="${row.id}" title="Modifica" href="https://${window.location.hostname}${gestioUrl}/base-dades-persones/modifica-persona/${row.slug}">
          <button type="button" class="button btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `https://elliot.cat/api/persones/get/llistatPersones`,
    containerId: 'taulaLlistatPersones',

    columns,

    filterKeys: ['nomComplet'],

    // 👇 IMPORTANTE: ahora filtramos por string seguro
    filterByField: 'grup',
  });
}
