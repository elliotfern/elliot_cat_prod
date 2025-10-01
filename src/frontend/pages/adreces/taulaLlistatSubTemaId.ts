import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { SubTema } from '../../types/SubTema';
import { Link } from '../../types/Link';
import { formatData } from '../../utils/formataData';

export async function taulaLlistatSubTemaId(id: string) {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Link>[] = [
    {
      header: 'Enllaç',
      field: 'nom',
      render: (_: unknown, row: Link) => `<a id="${row.id}" href="${row.web}" target="_blank">${row.nom}</a>`,
    },
    { header: 'Categoría', field: 'tema_ca' },

    { header: 'Tema', field: 'sub_tema_ca' },

    { header: 'Tipus', field: 'tipus_ca' },

    { header: 'Última actualització', field: 'dateModified', render: (_: unknown, row: Link) => formatData(row.dateModified) },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Link) => `<a id="${row.id}" href="https://${window.location.hostname}/gestio/adreces/modifica-link/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/adreces/get/subTemaId?=${id}`,
    containerId: 'taulaLlistatSubTemaId',
    columns,
    filterKeys: ['tema_ca'],
    filterByField: 'tema_ca',
  });
}
