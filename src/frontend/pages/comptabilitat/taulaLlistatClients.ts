import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Client } from '../../types/Client';
import { API_URLS } from '../../utils/apiUrls';
import { Button } from '../../ui/button';
import { INTRANET_URLS } from '../../utils/IntranetUrls';
import { mostrar } from '../../utils/renderText';

function getEstatBadgeClass(num: number): string {
  if (num <= 2) return 'bg-secondary';
  if (num <= 4) return 'bg-info';
  if (num <= 6) return 'bg-warning';
  if (num <= 8) return 'bg-primary';
  if (num <= 10) return 'bg-success';

  return 'bg-dark';
}

function renderEstatBadge(row: Client): string {
  return `
    <span class="badge ${getEstatBadgeClass(row.num)}">
      ${mostrar(row.estat, '-')}
    </span>
  `;
}

export async function taulaLlistatClients() {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Client>[] = [
    {
      header: 'Client',
      field: 'nom',
      render: (_value: unknown, row: Client) => `
        <a
          id="${row.id}"
          href="${INTRANET_URLS.COMPTABILITAT.CLIENT_FITXA_ID(row.id)}"
        >
          ${mostrar(row.nom, '')}
          ${mostrar(row.cognoms, '')}
        </a>
      `,
    },

    {
      header: 'Empresa',
      field: 'empresa',
      render: (_value: unknown, row: Client) => mostrar(row.empresa, '-'),
    },

    {
      header: 'Email',
      field: 'email',
      render: (_value: unknown, row: Client) => mostrar(row.email, '-'),
    },

    {
      header: 'Telèfon',
      field: 'telefon',
      render: (_value: unknown, row: Client) => mostrar(row.telefon, '-'),
    },

    {
      header: 'Estat',
      field: 'num',
      render: (_value: unknown, row: Client) => renderEstatBadge(row),
    },

    {
      header: 'Registre',
      field: 'registre',
      render: (_value: unknown, row: Client) => mostrar(row.registre, '-'),
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_value: unknown, row: Client) => Button.edit('Modificar', INTRANET_URLS.COMPTABILITAT.CLIENT_MODIFICA_ID(row.id)),
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.CLIENTS,
    containerId: 'taulaLlistatClients',
    columns,
  });
}
