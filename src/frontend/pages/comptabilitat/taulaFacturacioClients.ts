import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Factura } from '../../types/Factura';
import { API_URLS } from '../../utils/apiUrls';

const url = window.location.href;
const pageType = getPageType(url);

// 👉 Generador PDF (tipado y con estados de botón)
async function generatePDF(invoiceId: number, fileName?: string) {
  const btn = document.querySelector<HTMLButtonElement>(`.js-pdf[data-invoice-id="${CSS?.escape ? CSS.escape(String(invoiceId)) : String(invoiceId)}"]`);
  const prevLabel = btn?.textContent;
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Generant...';
  }

  try {
    const endpoint = API_URLS.GET.INVOICE_PDF(invoiceId);

    const res = await fetch(endpoint, { credentials: 'include' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const blob = await res.blob();
    // (no siempre viene type correcto, así que no lo validamos estrictamente)
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName || `invoice_${invoiceId}.pdf`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  } catch (e) {
    console.error('Error al generar el PDF:', e);
    alert("No s'ha pogut generar el PDF.");
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = prevLabel || 'PDF';
    }
  }
}

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
      render: (_: unknown, row: Factura) =>
        `<button type="button"
                  class="btn-petit btn-secondari js-pdf"
                  data-invoice-id="${row.id}"
                  data-file-name="invoice_${row.id}-${row.yearInvoice}.pdf">
            PDF
         </button>`,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Factura) => `
    <a href="https://${window.location.hostname}/gestio/comptabilitat/modifica-factura/${row.id}">
      <button class="btn-petit">Modifica</button>
    </a>`,
    });
  }

  renderDynamicTable({
    url: API_URLS.GET.FACTURACIO_CLIENTS,
    containerId: 'taulaLlistatFactures',
    columns,
    filterKeys: ['clientEmpresa', 'clientCognoms'],
    filterByField: 'any',
  });

  const container = document.getElementById('taulaLlistatFactures');
  container?.addEventListener('click', (ev) => {
    const target = ev.target as HTMLElement;
    const btn = target.closest<HTMLButtonElement>('.js-pdf');
    if (!btn) return;

    const idStr = btn.dataset.invoiceId;
    const fname = btn.dataset.fileName || undefined;
    if (!idStr) return;

    const idNum = Number(idStr);
    if (!Number.isInteger(idNum) || idNum <= 0) {
      console.warn('Id de factura no vàlid:', idStr);
      return;
    }

    generatePDF(idNum, fname);
  });
}
