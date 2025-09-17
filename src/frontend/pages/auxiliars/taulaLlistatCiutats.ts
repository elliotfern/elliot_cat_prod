import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Imatge } from '../../types/Imatge';
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
      field: 'city',
      render: (_: unknown, row: Ciutat) => `<a id="${row.id}" href="https://${window.location.hostname}${gestioUrl}/auxiliars/fitxa-ciutat/${row.city}">${row.city}</a>`,
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
    filterKeys: ['city'],
    filterByField: 'city',
  });
}
