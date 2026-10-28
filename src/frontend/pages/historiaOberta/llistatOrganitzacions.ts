import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { initDeleteHandlers, registerDeleteCallback, registerDeleteHandler } from '../../Presentation/Components/Table/handleDelete';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Organitzacio } from '../../types/Organitzacio';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { DOMAIN_WEB } from '../../utils/urls';

export async function taulaLlistatOrganitzacions(): Promise<void> {
  const reloadKey = 'organitzacions';
  const isAdmin = await getIsAdmin();

  registerDeleteHandler('organitzacio', async (id: string): Promise<void> => {
    await api.delete('historia/delete/organitzacio', { id });
  });

  registerDeleteCallback(reloadKey, () => taulaLlistatOrganitzacions());

  initDeleteHandlers();

  const columns: TaulaDinamica<Organitzacio>[] = [
    {
      header: 'Organització',
      field: 'organitzacio',
      render: (_: unknown, row: Organitzacio) => {
        return `
          <a href="${DOMAIN_WEB}/gestio/historia/fitxa-organitzacio/${encodeURIComponent(row.slug)}">
            ${row.organitzacio ? escapeHtml(row.organitzacio) : ''}
          </a>
        `;
      },
    },
    {
      header: 'Anys',
      field: 'any_fundacio',
      render: (_: unknown, row: Organitzacio) => {
        const anyFundacio = row.any_fundacio && row.any_fundacio !== '0000' ? row.any_fundacio : '';

        const anyDissolucio = row.any_dissolucio && row.any_dissolucio !== '0000' ? row.any_dissolucio : '';

        return [anyFundacio, anyDissolucio].filter(Boolean).join(' - ');
      },
    },
    {
      header: 'Tipus',
      field: 'tipus',
      render: (_: unknown, row: Organitzacio) => {
        return row.tipus ? escapeHtml(row.tipus) : '';
      },
    },
    {
      header: 'Etapa història',
      field: 'nomSubEtapa',
      render: (_: unknown, row: Organitzacio) => {
        return row.nomSubEtapa ? escapeHtml(row.nomSubEtapa) : '';
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Organitzacio) => {
        return `
          <div class="d-flex gap-2">
            <a
              href="${DOMAIN_WEB}/gestio/historia/modifica-organitzacio/${encodeURIComponent(row.id)}"
              class="btn btn-warning btn-sm"
            >
              Modifica
            </a>

            <button
              type="button"
              class="delete-button btn btn-danger btn-sm"
              data-id="${escapeHtml(row.id)}"
              data-delete-handler="organitzacio"
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
    url: 'historia/get/llistatOrganitzacions',
    containerId: reloadKey,
    columns,
    filterKeys: ['organitzacio'],
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
