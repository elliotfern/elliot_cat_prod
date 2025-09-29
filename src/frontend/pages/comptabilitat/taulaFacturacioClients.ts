import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getPageType } from '../../utils/urlPath';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Factura } from '../../types/Factura';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_WEB } from '../../utils/urls';

const url = window.location.href;
const pageType = getPageType(url);

// 👉 Generador PDF por idioma
async function generatePDF(invoiceId: number, lang: 'ca' | 'es' | 'en' | 'it', fileName?: string, btn?: HTMLButtonElement | null) {
  const prevLabel = btn?.textContent;
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Generant...';
  }

  try {
    const endpoint = API_URLS.GET.INVOICE_PDF(invoiceId, lang);
    const res = await fetch(endpoint, { credentials: 'include' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName || `invoice_${invoiceId}_${lang}.pdf`;
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

  const columns: TaulaDinamica<Factura>[] = [
    {
      header: 'Num',
      field: 'yearInvoice',
      render: (_: unknown, row: Factura) => `<a id="${row.id}" href="${DOMAIN_WEB}/gestio/comptabilitat/fitxa-factura-client/${row.id}">${row.id}/${row.yearInvoice}</a>`,
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
        `<div class="btn-group separat" role="group" aria-label="Descarregar PDF">
      <button type="button"
              class="btn-petit btn-secondari js-pdf"
              data-invoice-id="${row.id}"
              data-lang="ca"
              data-file-name="invoice_${row.id}-${row.yearInvoice}_ca.pdf">
        PDF (català)
      </button>
      <button type="button"
              class="btn-petit btn-secondari js-pdf"
              data-invoice-id="${row.id}"
              data-lang="es"
              data-file-name="invoice_${row.id}-${row.yearInvoice}_es.pdf">
        PDF (castellà)
      </button>
      <button type="button"
              class="btn-petit btn-secondari js-pdf"
              data-invoice-id="${row.id}"
              data-lang="en"
              data-file-name="invoice_${row.id}-${row.yearInvoice}_en.pdf">
        PDF (anglès)
      </button>
      <button type="button"
              class="btn-petit btn-secondari js-pdf"
              data-invoice-id="${row.id}"
              data-lang="it"
              data-file-name="invoice_${row.id}-${row.yearInvoice}_it.pdf">
        PDF (italià)
      </button>
    </div>
  `,
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
    const lang = (btn.dataset.lang || '').toLowerCase() as 'ca' | 'es' | 'en' | 'it';
    const fname = btn.dataset.fileName || undefined;
    if (!idStr || !lang || !['ca', 'es', 'en', 'it'].includes(lang)) return;

    const idNum = Number(idStr);
    if (!Number.isInteger(idNum) || idNum <= 0) {
      console.warn('Id de factura no vàlid:', idStr);
      return;
    }

    void generatePDF(idNum, lang, fname, btn);
  });
}
