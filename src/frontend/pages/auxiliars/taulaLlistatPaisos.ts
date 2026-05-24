import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Pais } from '../../types/Pais';

export async function taulaLlistatPaisos(): Promise<void> {
  const isAdmin = await getIsAdmin();

  const editHref = (id: string): string => `https://elliot.cat/gestio/auxiliars/modifica-pais/${id}`;

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
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Pais) => `<a title="Modifica" href="${editHref(row.id)}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `auxiliars/get/paisos`,
    containerId: 'taulaLlistatPaisos',
    columns,
    //filterKeys: ['pais_ca', 'pais_en'],
  });
}
