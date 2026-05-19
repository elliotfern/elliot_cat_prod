import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Client } from '../../types/Client';
import { API_URLS } from '../../utils/apiUrls';
import { Button } from '../../ui/button';
import { INTRANET_URLS } from '../../utils/IntranetUrls';

export async function taulaLlistatClients() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Client>[] = [
    {
      header: 'Client',
      field: 'clientNom',
      render: (_: unknown, row: Client) => `<a id="${row.id}" href="${INTRANET_URLS.COMPTABILITAT.CLIENT_FITXA_ID(row.id)}">${row.clientNom} ${row.clientCognoms}</a>`,
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
      field: 'estat',
      render: (_: unknown, row: Client) => `${row.estat}`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_, { id }) => Button.edit('Modificar', INTRANET_URLS.COMPTABILITAT.CLIENT_MODIFICA_ID(id)),
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
