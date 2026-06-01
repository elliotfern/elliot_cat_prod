import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { formatDataCatala } from '../../utils/formataData';
import { DOMAIN_WEB } from '../../utils/urls';

export interface Proveidor {
  id: string;
  client_id: string;
  concepte?: string;
  import?: string;
  data: string;
  nom?: string;
  cognoms?: string;
  empresa?: string;
  estat?: string;
  producte?: string;
  any?: string;

  created_at?: string;
  updated_at?: string;
}

export async function taulaPressupostos() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Proveidor>[] = [
    {
      header: 'Pressupost',
      field: 'concepte',
      render: (_: unknown, row: Proveidor) =>
        `<a href="${DOMAIN_WEB}/gestio/comptabilitat/fitxa-pressupost/${row.id}">
           ${row.concepte}
         </a>`,
    },

    {
      header: 'Client',
      field: 'concepte',
      render: (_: unknown, row: Proveidor) =>
        `<a href="${DOMAIN_WEB}/gestio/comptabilitat/fitxa-client/${row.client_id}">
           ${row.nom} ${row.cognoms} (${row.empresa})
         </a>`,
    },

    {
      header: 'Import',
      field: 'import',
      render: (_: unknown, row: Proveidor) => `<strong>${row.import} €</strong>`,
    },

    {
      header: 'Data enviament',
      field: 'data',
      render: (_: unknown, row: Proveidor) => `${formatDataCatala(row.data)}`,
    },

    { header: 'Estat', field: 'estat' },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Proveidor) => `
        <a href="${DOMAIN_WEB}/gestio/comptabilitat/modifica-pressupost/${row.id}">
          <button class="btn btn-warning btn-sm">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: `comptabilitat/get/pressupostos`,
    containerId: 'taulaLlistatPressupostos',
    columns,
    filterKeys: ['producte'],
    filterByField: 'any',
  });
}
