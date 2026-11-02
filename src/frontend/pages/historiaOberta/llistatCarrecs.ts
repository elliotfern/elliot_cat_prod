import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { initDeleteHandlers, registerDeleteCallback, registerDeleteHandler } from '../../Presentation/Components/Table/handleDelete';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Carrec } from '../../types/Carrec';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { DOMAIN_WEB } from '../../utils/urls';

export async function taulaLlistatCarrecs(): Promise<void> {
  const reloadKey = 'carrec';
  const isAdmin = await getIsAdmin();

  registerDeleteHandler('carrec', async (id: string): Promise<void> => {
    await api.delete('historia/delete/carrec', { id });
  });

  registerDeleteCallback(reloadKey, () => taulaLlistatCarrecs());

  initDeleteHandlers();

  const columns: TaulaDinamica<Carrec>[] = [
    {
      header: 'Càrrec / responsabilitat',
      field: 'nom',
      render: (_: unknown, row: Carrec) => {
        return `
          <a href="${DOMAIN_WEB}/gestio/historia/fitxa-carrec/${encodeURIComponent(row.id)}">
            ${row.nom ? escapeHtml(row.nom) : ''}
          </a>
        `;
      },
    },

    {
      header: 'Persona',
      field: 'organitzacio',
      render: (_: unknown, row: Carrec) => {
        const nom = row.nomPersona ? row.nomPersona : '';
        const cognoms = row.cognoms ? row.cognoms : '';
        return `
          <a href="${DOMAIN_WEB}/gestio/base-dades-persones/fitxa-persona/${encodeURIComponent(row.slugPersona)}">
            ${nom ? escapeHtml(nom) : ''} ${cognoms ? escapeHtml(cognoms) : ''}
          </a>
        `;
      },
    },

    {
      header: 'Organització / institució',
      field: 'organitzacio',
      render: (_: unknown, row: Carrec) => {
        return `
          <a href="${DOMAIN_WEB}/gestio/historia/fitxa-organitzacio/${encodeURIComponent(row.slugOrganitzacio)}">
            ${row.organitzacio ? escapeHtml(row.organitzacio) : ''}
          </a>
        `;
      },
    },

    {
      header: 'Anys',
      field: 'inici',
      render: (_: unknown, row: Carrec) => {
        const anyFundacio = row.inici ? row.inici : '';

        const anyDissolucio = row.fi ? row.fi : '';

        return [anyFundacio, anyDissolucio].filter(Boolean).join(' - ');
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Carrec) => {
        return `
          <div class="d-flex gap-2">
            <a
              href="${DOMAIN_WEB}/gestio/historia/modifica-carrec/${encodeURIComponent(row.id)}"
              class="btn btn-warning btn-sm"
            >
              Modifica
            </a>

            <button
              type="button"
              class="delete-button btn btn-danger btn-sm"
              data-id="${escapeHtml(row.id)}"
              data-delete-handler="carrec"
              data-reload-callback="${reloadKey}"
            >
              Elimina
            </button>
          </div>
        `;
      },
    });
  }

  renderDynamicTable({
    url: 'historia/get/llistatCarrecs',
    containerId: 'carrecsLlista',
    columns,
    filterKeys: ['nom'],
  });
}

function escapeHtml(input: unknown): string {
  return String(input ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}
