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

export function renderClientFactures(
  clientId: string
) {

  const columns: TaulaDinamica<FacturaClient>[] = [

    {
      header: 'Factura',
      field: 'numero_factura',

      render: (_: unknown, row: FacturaClient) => `
        <strong>
          ${row.numero_factura}
        </strong>
      `,
    },

    {
      header: 'Concepte',
      field: 'concepte',

      render: (_: unknown, row: FacturaClient) =>
        row.concepte ?? '',
    },

    {
      header: 'Data factura',
      field: 'data_factura',

      render: (_: unknown, row: FacturaClient) =>
        row.data_factura
          ? new Date(
              row.data_factura
            ).toLocaleDateString('ca-ES')
          : '',
    },

    {
      header: 'Venciment',
      field: 'data_venciment',

      render: (_: unknown, row: FacturaClient) =>
        row.data_venciment
          ? new Date(
              row.data_venciment
            ).toLocaleDateString('ca-ES')
          : '',
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

    url:
      `https://elliot.cat/api/comptabilitat/get/facturesClientId?id=${clientId}`,

    containerId: 'clientFactures',

    columns,

    filterKeys: [
      'numero_factura',
      'concepte',
    ],

    renderHeader: (result: any) => {

      const total =
        result?.data?.totals?.total_facturat ?? 0;

      return `
        <div class="card shadow-sm mb-4">

          <div class="card-body">

            <h5 class="card-title mb-3">
              Resum de facturació
            </h5>

            <div class="row">

              <div class="col-md-4">

                <div class="border rounded p-3 bg-light">

                  <small class="text-muted d-block">
                    Total facturat
                  </small>

                  <strong class="fs-4">
                    ${Number(total).toFixed(2)} €
                  </strong>

                </div>

              </div>

            </div>

          </div>

        </div>
      `;
    },
  });
}