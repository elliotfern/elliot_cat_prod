import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Client } from '../../types/Client';
import { API_URLS } from '../../utils/apiUrls';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaLlistatClients() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Client>[] = [
    {
      header: 'Client',
      field: 'clientNom',
      render: (_: unknown, row: Client) => `<a id="${row.id}" href="https://${window.location.hostname}/gestio/comptabilitat/fitxa-client/${row.id}">${row.clientNom} ${row.clientCognoms}</a>`,
    },

    {
      header: 'Empresa',
      field: 'clientEmpresa',
      render: (_: unknown, row: Client) => `${row.clientEmpresa}`,
    },

    {
      header: 'Email',
      field: 'clientEmail',
      render: (_: unknown, row: Client) => `${row.clientEmail}`,
    },

    {
      header: 'Estat',
      field: 'estatNom',
      render: (_: unknown, row: Client) => `${row.estatNom}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Client) => `<a a id="${row.id}" title="Modifica" href="https://${window.location.hostname}/gestio/comptabilitat/modifica-client/${row.id}"><button class="btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.CLIENTS,
    containerId: 'taulaLlistatClients',
    columns,
    filterKeys: ['pais_ca'],
    //filterByField: 'pais_ca',
  });
}
