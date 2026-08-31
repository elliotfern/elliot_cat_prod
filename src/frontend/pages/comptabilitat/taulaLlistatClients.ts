import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Client } from '../../types/Client';
import { API_URLS } from '../../utils/apiUrls';
import { Button } from '../../Presentation/Components/Button/Button';
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
      field: 'tel_1',
      render: (_value: unknown, row: Client) => mostrar(row.tel_1, '-'),
    },

    {
      header: 'Estat',
      field: 'num',
      render: (_value: unknown, row: Client) => renderEstatBadge(row),
    },
  ];

  if (isAdmin) {
    columns.push(
      {
        header: '',
        field: 'id',
        render: (_, { id }) => Button.edit('Modifica contacte', INTRANET_URLS.CONTACTES.CONTACTE_MODIFICA_ID(id)),
      },
      {
        header: '',
        field: 'id',
        render: (_, { id }) => Button.edit2('Modifica client', INTRANET_URLS.COMPTABILITAT.CLIENT_MODIFICA_ID(id)),
      }
    );
  }

  renderDynamicTable({
    url: API_URLS.GET.CLIENTS,
    containerId: 'taulaLlistatClients',
    columns,
  });
}
