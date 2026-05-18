import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { TaulaDinamica } from '../../types/TaulaDinamica';

interface PressupostClient {
  id: string;
  concepte: string | null;
  client_id: string;
  servei_id: string;
  estat_id: string;
  import: number;
  data: string;
  created_at: string;
  modified_at: string;

  estatNom: string | null;
  producte: string | null;
  any: number;
}

export function renderClientPressupostos(clientId: string) {
  const columns: TaulaDinamica<PressupostClient>[] = [
    {
      header: 'Concepte',
      field: 'concepte',
      render: (_: unknown, row: PressupostClient) => `<strong>${row.concepte ?? ''}</strong>`,
    },
    {
      header: 'Producte',
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
    url: `https://elliot.cat/api/comptabilitat/get/pressupostosClientId?id=${clientId}`,
    containerId: 'clientPresupostos',
    columns,
    filterKeys: ['concepte', 'producte'],
  });
}
