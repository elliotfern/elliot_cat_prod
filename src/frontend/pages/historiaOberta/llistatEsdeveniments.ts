import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { initDeleteHandlers, registerDeleteCallback, registerDeleteHandler } from '../../Presentation/Components/Table/handleDelete';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { Esdeveniment } from '../../types/Esdeveniment';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { DOMAIN_WEB } from '../../utils/urls';

export async function taulaLlistatEsdeveniments(): Promise<void> {
  const reloadKey = 'esdeveniment';
  const isAdmin = await getIsAdmin();

  registerDeleteHandler('esdeveniment', async (id: string): Promise<void> => {
    await api.delete('historia/delete/esdeveniment', { id });
  });

  registerDeleteCallback(reloadKey, () => taulaLlistatEsdeveniments());

  initDeleteHandlers();

  const columns: TaulaDinamica<Esdeveniment>[] = [
    {
      header: 'Esdeveniment',
      field: 'esdeNom',
      render: (_: unknown, row: Esdeveniment) => {
        return `
          <a href="${DOMAIN_WEB}/gestio/historia/fitxa-esdeveniment/${encodeURIComponent(row.slug)}">
            ${row.esdeNom ? escapeHtml(row.esdeNom) : ''}
          </a>
        `;
      },
    },
    {
      header: 'Anys',
      field: 'esdeDataIAny',
      render: (_: unknown, row: Esdeveniment) => {
        const anyFundacio = row.esdeDataIAny ? row.esdeDataIAny : '';

        const anyDissolucio = row.esdeDataFAny ? row.esdeDataFAny : '';

        return [anyFundacio, anyDissolucio].filter(Boolean).join(' - ');
      },
    },
    {
      header: 'Etapa història',
      field: 'etapaNom',
      render: (_: unknown, row: Esdeveniment) => {
        return row.etapaNom ? escapeHtml(row.etapaNom) : '';
      },
    },
    {
      header: 'Període',
      field: 'nomSubEtapa',
      render: (_: unknown, row: Esdeveniment) => {
        return row.nomSubEtapa ? escapeHtml(row.nomSubEtapa) : '';
      },
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Esdeveniment) => {
        return `
          <div class="d-flex gap-2">
            <a
              href="${DOMAIN_WEB}/gestio/historia/modifica-esdeveniment/${encodeURIComponent(row.id)}"
              class="btn btn-warning btn-sm"
            >
              Modifica
            </a>

            <button
              type="button"
              class="delete-button btn btn-danger btn-sm"
              data-id="${escapeHtml(row.id)}"
              data-delete-handler="esdeveniment"
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
    url: 'historia/get/llistatEsdevenimentsTaula',
    containerId: 'llistatEsdeveniments',
    columns,
    filterKeys: ['esdeNom'],
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
