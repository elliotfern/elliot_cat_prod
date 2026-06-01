import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Link } from '../../types/Link';

export async function taulaLlistatLinks() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Link>[] = [
    {
      header: 'Enllaç',
      field: 'nom',
      render: (_: unknown, row: Link) => `<a id="${row.id}" href="${row.web}" target="_blank">${row.nom}</a>`,
    },
    { header: 'Categoría', field: 'tema' },

    { header: 'Tema', field: 'sub_tema' },

    { header: 'Tipus', field: 'tipus' },

    { header: 'Última actualització', field: 'dateModified', render: (_: unknown, row: Link) => formatData(row.dateModified) },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Link) => `<a id="${row.id}" title="Show movie details" href="/gestio/adreces/modifica-link/${row.id}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `adreces/get/llistatLinks`,
    containerId: 'taulaLlistatLinks',
    columns,
    filterKeys: ['nom'],
    filterByField: 'tema',
  });
}
