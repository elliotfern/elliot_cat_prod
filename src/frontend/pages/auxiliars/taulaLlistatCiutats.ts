import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Ciutat } from '../../types/Ciutat';

export async function taulaLlistatCiutats() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Ciutat>[] = [
    {
      header: 'Ciutat',
      field: 'ciutat',
      render: (_: unknown, row: Ciutat) => `<a id="${row.id}" href="/gestio/auxiliars/fitxa-ciutat/${row.id}">${row.ciutat}</a>`,
    },

    {
      header: 'Ciutat (català)',
      field: 'ciutat_ca',
      render: (_: unknown, row: Ciutat) => {
        if (row.ciutat_ca == null || row.ciutat_ca.trim() === '') {
          return '';
        }

        return `<a id="${row.id}" href="/gestio/auxiliars/fitxa-ciutat/${row.id}">${row.ciutat_ca}</a>`;
      },
    },

    {
      header: 'País',
      field: 'pais',
      render: (_: unknown, row: Ciutat) => `<a id="${row.pais.id}" href="/gestio/auxiliars/fitxa-pais/${row.pais.id}">${row.pais.pais_ca}</a>`,
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
      render: (_: unknown, row: Ciutat) => `<a a id="${row.id}" title="Modifica" href="/gestio/auxiliars/modifica-ciutat/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `auxiliars/get/llistatCiutats`,
    containerId: 'taulaLlistatCiutats',
    columns,
    filterKeys: ['ciutat'],
    filterByField: 'pais.pais_ca',
  });
}
