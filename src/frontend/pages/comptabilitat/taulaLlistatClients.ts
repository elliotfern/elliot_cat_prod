import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Client } from '../../types/Client';
import { API_URLS } from '../../utils/apiUrls';
import { Button } from '../../ui/button';
import { INTRANET_URLS } from '../../utils/IntranetUrls';
import { mostrar } from '../../utils/renderText';

function getEstatBadgeClass(ordre: number): string {
  if (ordre <= 2) return 'bg-secondary';
  if (ordre <= 4) return 'bg-info';
  if (ordre <= 6) return 'bg-warning';
  if (ordre <= 8) return 'bg-primary';
  if (ordre <= 10) return 'bg-success';
  return 'bg-dark';
}

export async function taulaLlistatClients() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Client>[] = [
    {
      header: 'Client',
      field: 'clientNom',
      render: (_: unknown, row: Client) => `<a id="${row.id}" href="${INTRANET_URLS.COMPTABILITAT.CLIENT_FITXA_ID(row.id)}">
      ${mostrar(row.clientNom, '')} ${mostrar(row.clientCognoms, '')}</a>`,
    },

    {
      header: 'Empresa',
      field: 'clientEmpresa',
      render: (_: unknown, row: Client) => `${mostrar(row.clientEmpresa, '-')}`,
    },

    {
      header: 'Email',
      field: 'clientEmail',
      render: (_: unknown, row: Client) => `${row.clientEmail}`,
    },

    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: Client) => {
        const classe = getEstatBadgeClass(row.ordre);
        return `
          <span class="badge ${classe}">
            ${row.estat}
          </span>`;
      },
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
