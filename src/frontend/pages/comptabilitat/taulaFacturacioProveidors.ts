import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { API_URLS } from '../../utils/apiUrls';

const RECEPTORS: Record<number, string> = {
  1: 'Hispano Atlantic Consulting Ltd (juliol 2017 - octubre 2022)',
  2: 'Autònom Irlanda (1 novembre 2022 - 29 març 2026)',
  3: 'Partita Iva Itàlia (30 març 2026 - )',
};

export function renderTitolReceptor(receptorId: number) {
  const container = document.getElementById('titolTipusFactura');
  if (!container) return;

  const titol = RECEPTORS[receptorId] || 'Receptor desconegut';
  container.innerHTML = `<h3>${titol}</h3>`;
}

export async function taulaDespeses(receptorId: number) {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<any>[] = [
    { header: 'Data', field: 'data', render: (_: unknown, row: any) => formatData(row.data) },
    { header: 'Concepte', field: 'concepte' },
    {
      header: 'Proveïdor',
      field: 'proveidorNom',
      render: (_: unknown, row: any) => `
        <a href="https://${window.location.hostname}/gestio/comptabilitat/fitxa-proveidor/${row.proveidorId}">
          ${row.proveidorNom}
        </a>`,
    },
    { header: 'Categoria', field: 'nomCategoria' },
    { header: 'Base', field: 'base_imposable', render: (_: unknown, row: any) => `${row.base_imposable}€` },
    { header: 'IVA', field: 'import_iva', render: (_: unknown, row: any) => `${row.import_iva}€` },
    { header: 'Total', field: 'total', render: (_: unknown, row: any) => `<strong>${row.total}€</strong>` },
    {
      header: 'Pagat',
      field: 'pagat',
      render: (_: unknown, row: any) =>
        `<button class="btn-petit ${row.pagat ? 'btn-primari' : 'btn-secondari'}">
          ${row.pagat ? 'Pagat' : 'Pendent'}
        </button>`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: any) => `
        <a href="https://${window.location.hostname}/gestio/comptabilitat/modifica-despesa/${row.id}">
          <button class="btn-petit">Modifica</button>
        </a>`,
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.DESPESES(receptorId),
    containerId: 'taulaLlistatFacturesProveidors',
    columns,
    filterKeys: ['concepte', 'nomCategoria'],
    filterByField: 'any',
  });

  renderTitolReceptor(receptorId);
}
