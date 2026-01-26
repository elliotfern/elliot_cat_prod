import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Ciutat } from '../../types/Ciutat';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatCiutats() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Ciutat>[] = [
    {
      header: 'Ciutat',
      field: 'ciutat',
      render: (_: unknown, row: Ciutat) => `<a id="${row.id}" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-ciutat/${row.id}">${row.ciutat}</a>`,
    },

    {
      header: 'Ciutat (català)',
      field: 'ciutat_ca',
      render: (_: unknown, row: Ciutat) => {
        if (row.ciutat_ca == null || row.ciutat_ca.trim() === '') {
          return '';
        }

        return `<a id="${row.id}" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-ciutat/${row.id}">${row.ciutat_ca}</a>`;
      },
    },

    {
      header: 'País',
      field: 'pais_ca',
      render: (_: unknown, row: Ciutat) => `<a id="${row.idPais}" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-pais/${row.idPais}">${row.pais_ca}</a>`,
    },

    {
      header: 'Última actualització',
      field: 'updated_at',
      render: (_: unknown, row: Ciutat) => `${formatData(row.updated_at)}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Ciutat) => `<a a id="${row.id}" title="Modifica" href="https://${window.location.hostname}${gestioUrl}/auxiliars/modifica-ciutat/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/auxiliars/get/ciutats`,
    containerId: 'taulaLlistatCiutats',
    columns,
    filterKeys: ['ciutat'],
    filterByField: 'pais_ca',
  });
}
