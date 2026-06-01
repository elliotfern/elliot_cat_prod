import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { PressupostClient } from '../../types/Client';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export function renderClientPressupostos(clientId: string) {
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
      field: 'estatNom',
      render: (_: unknown, row: PressupostClient) => `<span class="badge bg-secondary">${row.estatNom ?? ''}</span>`,
    },
    {
      header: 'Import',
      field: 'import',
      render: (_: unknown, row: PressupostClient) => (row.import ? `${row.import.toFixed(2)} €` : ''),
    },
    {
      header: 'Data',
      field: 'data',
      render: (_: unknown, row: PressupostClient) => (row.data ? new Date(row.data).toLocaleDateString('ca-ES') : ''),
    },
    {
      header: 'Any',
      field: 'any',
      render: (_: unknown, row: PressupostClient) => `${row.any ?? ''}`,
    },
  ];

  renderDynamicTable({
    url: `comptabilitat/get/pressupostosClientId?id=${clientId}`,
    containerId: 'clientPresupostos',
    columns,
    filterKeys: ['concepte', 'producte'],
    rowsPerPage: 9999,

    renderHeader: () => `
    <div class="mb-3">
      <h2 class="h4 mb-0">Pressupostos client:</h2>
    </div>
  `,
  });
}
