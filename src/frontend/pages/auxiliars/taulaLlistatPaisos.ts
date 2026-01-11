import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Pais } from '../../types/Pais';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatPaisos(): Promise<void> {
  const isAdmin = await getIsAdmin();

  // Si no fas servir slug, millor eliminar-lo
  // const slug = isAdmin ? pageType[3] : pageType[2];

  const gestioUrl = isAdmin ? '/gestio' : '';

  const editHref = (id: string): string => `https://${window.location.hostname}${gestioUrl}/auxiliars/modifica-pais/${id}`;

  const columns: TaulaDinamica<Pais>[] = [
    {
      header: 'País (català)',
      field: 'pais_ca',
      render: (_: unknown, row: Pais) => `<a id="pais-${row.id}-ca" href="${editHref(row.id)}">${row.pais_ca}</a>`,
    },
    {
      header: 'País (anglès)',
      field: 'pais_en',
      render: (_: unknown, row: Pais) => `<a id="pais-${row.id}-en" href="${editHref(row.id)}">${row.pais_en}</a>`,
    },
    {
      header: 'Última actualització',
      field: 'updated_at',
      render: (_: unknown, row: Pais) => `${formatData(row.updated_at)}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Pais) => `<a title="Modifica" href="${editHref(row.id)}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://api.elliot.cat/api/paisos`,
    containerId: 'taulaLlistatPaisos',
    columns,
    filterKeys: ['pais_ca', 'pais_en'],
  });
}
