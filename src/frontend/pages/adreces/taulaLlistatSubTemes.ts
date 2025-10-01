import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { SubTema } from '../../types/SubTema';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatSubTemes() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<SubTema>[] = [
    {
      header: 'Subtema',
      field: 'sub_tema_ca',
      render: (_: unknown, row: SubTema) => `<a id="${row.id}" title="Show category" href="https://${window.location.host}${gestioUrl}/adreces/subtema/${row.id}">${row.sub_tema_ca}</a>`,
    },
    { header: 'Categoría', field: 'tema_ca' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: SubTema) => `<a id="${row.id}" title="Show movie details" href="https://${window.location.hostname}${gestioUrl}/adreces/modifica-tema/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/llistatSubTemes`,
    containerId: 'taulaLlistatSubTemes',
    columns,
    filterKeys: ['tema_ca'],
    filterByField: 'tema_ca',
  });
}
