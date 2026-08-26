import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { formatDataCatala } from '../../utils/formataData';
import { formatEuro } from '../../utils/locales/formatEuro';
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
  num: string;

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
      render: (_: unknown, row: Proveidor) => `<strong>${formatEuro(row.import)}</strong>`,
    },

    {
      header: 'Data enviament',
      field: 'data',
      render: (_: unknown, row: Proveidor) => `${formatDataCatala(row.data)}`,
    },

    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: Proveidor) => {
        const colors: Record<string, string> = {
          '01': 'bg-secondary',
          '02': 'bg-info text-dark',
          '03': 'bg-info text-dark',
          '04': 'bg-primary',
          '05': 'bg-success',
          '06': 'bg-danger',
          '07': 'bg-warning text-dark',
          '08': 'bg-primary',
          '09': 'bg-info text-dark',
          '10': 'bg-success',
          '11': 'bg-danger',
          '99': 'bg-dark',
        };

        const color = colors[String(row.num)] ?? 'bg-secondary';

        return `<span class="badge ${color}">${row.estat}</span>`;
      },
    },
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
