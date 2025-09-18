import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Pais } from '../../types/Pais';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatPaisos() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Pais>[] = [
    {
      header: 'Pais (català)',
      field: 'pais_ca',
      render: (_: unknown, row: Pais) => `<a id="${row.id}" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-ciutat/${row.id}">${row.pais_ca}</a>`,
    },

    {
      header: 'País (anglès)',
      field: 'pais_en',
      render: (_: unknown, row: Pais) => `<a id="${row.id}" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-ciutat/${row.id}">${row.pais_en}</a>`,
    },

    {
      header: 'Última actualització',
      field: 'updated_at',
      render: (_: unknown, row: Pais) => `${row.updated_at}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Pais) => `<a a id="${row.id}" title="Modifica" href="https://${window.location.hostname}${gestioUrl}/auxiliars/modifica-pais/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://api.elliot.cat/api/paisos`,
    containerId: 'taulaLlistatPaisos',
    columns,
    filterKeys: ['pais_ca'],
    //filterByField: 'pais_ca',
  });
}
