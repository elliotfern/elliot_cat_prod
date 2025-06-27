import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Factura } from '../../types/Factura';

const url = window.location.href;
const pageType = getPageType(url);

export async function taulaFacturacioClients() {
  const isAdmin = await getIsAdmin();
  let slug: string = '';
  let gestioUrl: string = '';

  if (isAdmin) {
    slug = pageType[3];
    gestioUrl = '/gestio';
  } else {
    slug = pageType[2];
  }

  const columns: TaulaDinamica<Factura>[] = [
    {
      header: 'Num',
      field: 'yearInvoice',
      render: (_: unknown, row: Factura) => `<a id="${row.id}" href="#">${row.id}/${row.yearInvoice}</a>`,
    },
    {
      header: 'Empresa',
      field: 'clientEmpresa',
      render: (_: unknown, row: Factura) => `${row.clientEmpresa ? row.clientEmpresa : `${row.clientNom} ${row.clientCognoms}`}`,
    },
    {
      header: 'Data factura',
      field: 'facData',
      render: (_: unknown, row: Factura) => {
        const inici = formatData(row.facData);
        return `${inici}`;
      },
    },
    {
      header: 'Concepte',
      field: 'facConcepte',
    },
    {
      header: 'Total',
      field: 'facTotal',
      render: (_: unknown, row: Factura) => `${row.facTotal}€`,
    },
    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: Factura) => `<button type="button" class="btn-petit btn-primari">${row.estat}</button>`,
    },
    {
      header: 'PDF',
      field: 'id',
      render: (_: unknown, row: Factura) => `<button type="button" class="btn-petit btn-secondari" onclick="generatePDF(${row.id})" id="pdfButton${row.id}">PDF</button>`,
    },
    {
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Factura) => `
    <a href="https://${window.location.hostname}/gestio/viatges/modifica-viatge/${row.id}">
      <button class="btn-petit">Modifica</button>
    </a>`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Factura) => `<a id="${row.id}" title="Show movie details" href="https://${window.location.hostname}${gestioUrl}/cinema/modifica-pelicula/${row.slug}"><button type="button" class="button btn-petit">Modifica</button></a>`,
    });
  }

  renderDynamicTable({
    url: `https://${window.location.host}/api/accounting/get/?type=accounting-elliotfernandez-customers-invoices`,
    containerId: 'taulaLlistatFactures',
    columns,
    filterKeys: ['clientEmpresa', 'clientCognoms'],
    filterByField: 'any',
  });
}
