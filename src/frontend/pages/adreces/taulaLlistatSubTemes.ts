import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { SubTema } from '../../types/SubTema';
import { DOMAIN_WEB } from '../../utils/urls';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatSubTemes() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<SubTema>[] = [
    {
      header: 'Subtema',
      field: 'sub_tema_ca',
      render: (_: unknown, row: SubTema) => `<a id="${row.id}" href="${DOMAIN_WEB}/gestio/adreces/llistat-subtema/${row.id}">${row.sub_tema_ca}</a>`,
    },
    { header: 'Categoría', field: 'tema_ca' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: SubTema) => `<a id="${row.id}" href="${DOMAIN_WEB}/gestio/adreces/modifica-subtema/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
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
