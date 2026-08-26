import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { PressupostClient } from '../../types/Client';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Button } from '../../ui/button';
import { INTRANET_URLS } from '../../utils/IntranetUrls';
import { formatEuro } from '../../utils/locales/formatEuro';

export async function renderClientPressupostos(clientId: string) {
  const isAdmin = await getIsAdmin();
  const columns: TaulaDinamica<PressupostClient>[] = [
    {
      header: 'Concepte',
      field: 'concepte',
      render: (_: unknown, row: PressupostClient) => `<strong><a href="/gestio/comptabilitat/fitxa-pressupost/${row.id}">${row.concepte ?? ''}</a></strong>`,
    },

    {
      header: 'Servei',
      field: 'producte',
      render: (_: unknown, row: PressupostClient) => `${row.producte ?? ''}`,
    },

    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: PressupostClient) => `<span class="badge bg-secondary">${row.estat ?? ''}</span>`,
    },

    {
      header: 'Import',
      field: 'import',
      render: (_: unknown, row: PressupostClient) => (row.import != null ? `${formatEuro(row.import)}` : ''),
    },

    {
      header: 'Data',
      field: 'data',
      render: (_: unknown, row: PressupostClient) => (row.data ? new Date(row.data).toLocaleDateString('ca-ES') : ''),
    },
  ];

  if (isAdmin) {
    columns.push({
      header: '',
      field: 'id',
      render: (_, { id }) => Button.edit('Modifica', INTRANET_URLS.COMPTABILITAT.PRESSUPOST_MODIFICA_ID(id)),
    });
  }

  renderDynamicTable({
    url: `comptabilitat/get/pressupostosClientId?id=${clientId}`,
    containerId: 'clientPressupostos',
    columns,
    filterKeys: ['concepte', 'producte'],
    dataKey: 'pressupostos',
    rowsPerPage: 9999,

    renderHeader: () => `
      <div class="mb-3">
        <h2 class="h4 mb-0">Pressupostos client</h2>
      </div>
    `,
  });
}
