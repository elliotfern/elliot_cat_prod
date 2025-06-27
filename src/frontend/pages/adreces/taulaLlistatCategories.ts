import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
// import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Categoria } from '../../types/Categoria';
import { TaulaDinamica } from '../../types/TaulaDinamica';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatTemes() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Categoria>[] = [
    {
      header: 'Tema',
      field: 'tema',
      render: (_: unknown, row: Categoria) => `<a id="${row.idTema}" title="Show category" href="https://${window.location.host}${gestioUrl}/adreces/tema/${row.idTema}">${row.tema}</a>`,
    },
    { header: 'Categoría', field: 'genre' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Categoria) => `<a id="${row.idTema}" title="Show movie details" href="https://${window.location.hostname}${gestioUrl}/adreces/modifica-tema/${row.slug}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/?type=all-topics`,
    containerId: 'taulaLlistatCategories',
    columns,
    filterKeys: ['tema'],
    filterByField: 'genre',
  });
}
