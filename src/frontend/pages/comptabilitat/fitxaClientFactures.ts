import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';

interface FacturaClient {
  id: string;
  numero_factura: string;
  concepte: string | null;

  data_factura: string;
  data_venciment: string | null;

  base_imposable: number;
  import_iva: number;
  total_factura: number;

  any: string;

  estat: string | null;
  tipusNom: string | null;
  ivaPercen: number | null;
}

export function renderClientFactures(clientId: string) {
  const columns: TaulaDinamica<FacturaClient>[] = [
    {
      header: 'Factura',
      field: 'numero_factura',

      render: (_: unknown, row: FacturaClient) => `
        <strong><a href="https://elliot.cat/gestio/comptabilitat/fitxa-factura">${row.numero_factura}</a></strong>
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
    url: `https://elliot.cat/api/comptabilitat/get/facturesClientId?id=${clientId}`,
    containerId: 'clientFactures',
    columns,
    filterKeys: ['numero_factura', 'concepte'],
    dataKey: 'factures',
    rowsPerPage: 9999,

    renderHeader: (result: any) => {
      const total = result?.data?.totals?.total_facturat ?? 0;

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
