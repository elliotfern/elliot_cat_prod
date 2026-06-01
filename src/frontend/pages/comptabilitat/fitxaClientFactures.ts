import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { FacturaClient } from '../../types/Client';
import { TaulaDinamica } from '../../types/TaulaDinamica';

export function renderClientFactures(clientId: string) {
  const columns: TaulaDinamica<FacturaClient>[] = [
    {
      header: 'Factura',
      field: 'numero_factura',

      render: (_: unknown, row: FacturaClient) => `
        <strong><a href="/gestio/comptabilitat/fitxa-factura-client/${row.id}">${row.numero_factura}</a></strong>
      `,
    },

    {
      header: 'Concepte',
      field: 'concepte',

      render: (_: unknown, row: FacturaClient) => row.concepte ?? '',
    },

    {
      header: 'Data factura',
      field: 'data_factura',

      render: (_: unknown, row: FacturaClient) => (row.data_factura ? new Date(row.data_factura).toLocaleDateString('ca-ES') : ''),
    },

    {
      header: 'Venciment',
      field: 'data_venciment',

      render: (_: unknown, row: FacturaClient) => (row.data_venciment ? new Date(row.data_venciment).toLocaleDateString('ca-ES') : ''),
    },

    {
      header: 'Total',
      field: 'total_factura',

      render: (_: unknown, row: FacturaClient) => `
        <strong>
          ${Number(row.total_factura).toFixed(2)} €
        </strong>
      `,
    },

    {
      header: 'Estat',
      field: 'estat',

      render: (_: unknown, row: FacturaClient) => `
        <span class="badge bg-info">
          ${row.estat ?? ''}
        </span>
      `,
    },
  ];

  renderDynamicTable({
    url: `comptabilitat/get/facturesClientId?id=${clientId}`,
    containerId: 'clientFactures',
    columns,
    filterKeys: ['numero_factura', 'concepte'],
    dataKey: 'factures',
    rowsPerPage: 9999,

    renderHeader: ({ raw }: any) => {
      const total = raw?.totals?.total_facturat ?? 0;

      return `
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="h4 mb-0">Factures client</h2>
          <small class="text-muted">
            Total facturat acumulat
          </small>
        </div>

        <div class="text-end">
          <div class="fs-4 fw-bold text-success">
            ${Number(total).toFixed(2)} €
          </div>
        </div>
      </div>
    `;
    },
  });
}
