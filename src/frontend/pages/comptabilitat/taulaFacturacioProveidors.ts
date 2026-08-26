import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { API_URLS } from '../../utils/apiUrls';
import { Button } from '../../ui/button';
import { INTRANET_URLS } from '../../utils/IntranetUrls';

export const RECEPTORS: Record<string, string> = {
  '019e3ebaf71370c2860a40a79fb5ad7b': 'Hispano Atlantic Consulting Ltd (juliol 2017 - octubre 2022)',

  '019e3ebaf71370c2860a40a7a078beb4': 'Autònom Irlanda (1 novembre 2022 - 29 març 2026)',

  '019e3ebaf71370c2860a40a7a15db129': 'Partita Iva Itàlia (30 març 2026 - )',

  '019e3ebaf71370c2860a40a7a241e107': 'Despeses personals',
};

export function renderTitolReceptor(receptorId: string) {
  const container = document.getElementById('titolTipusFactura');
  if (!container) return;

  const titol = RECEPTORS[receptorId] || 'Receptor desconegut';
  container.innerHTML = `<h3>${titol}</h3>`;
}

export async function taulaDespeses(receptorId: string, tipus_despesa: string) {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<any>[] = [
    { header: 'Data', field: 'data', render: (_: unknown, row: any) => formatData(row.data) },
    { header: 'Concepte', field: 'concepte' },
    {
      header: 'Proveïdor',
      field: 'proveidorNom',
      render: (_: unknown, row: any) => `
        <a href="/gestio/comptabilitat/fitxa-proveidor/${row.proveidorId}">
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
        `<button class="btn btn-sm ${row.pagat ? 'btn-secondary' : 'btn-primary'}">
          ${row.pagat ? 'Pagat' : 'Pendent'}
        </button>`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_, { id }) => Button.edit('Modifica', INTRANET_URLS.COMPTABILITAT.FACTURA_DESPESA_MODIFICA(id)),
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.DESPESES(receptorId, tipus_despesa),
    containerId: 'taulaLlistatFacturesProveidors',
    columns,
    filterKeys: ['concepte', 'nomCategoria'],
    filterByField: 'any',
  });

  renderTitolReceptor(receptorId);
}
