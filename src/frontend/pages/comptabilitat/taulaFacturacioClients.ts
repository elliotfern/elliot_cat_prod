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

async function sendInvoiceEmail(invoiceId: number, lang: 'ca' | 'es' | 'en' | 'it', btn?: HTMLButtonElement | null) {
  const prev = btn?.textContent;
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Enviant…';
  }

  try {
    const endpoint = API_URLS.POST.ENVIAR_FACTURA_EMAIL(invoiceId, lang);
    const res = await fetch(endpoint, {
      method: 'POST',
      credentials: 'include',
      headers: { Accept: 'application/json' },
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    if (json?.status !== 'success') throw new Error(json?.message || 'Error API');

    alert('Enviat correctament ✅');
  } catch (e) {
    console.error('Error enviant el correu:', e);
    alert("No s'ha pogut enviar el correu.");
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = prev || 'Enviar';
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

    {
      header: 'Enviar email',
      field: 'id',
      render: (_: unknown, row: Factura) =>
        `<div class="btn-group separat" role="group" aria-label="Enviar factura per email">
      <button type="button" class="btn-petit btn-secondari js-send"
              data-invoice-id="${row.id}" data-lang="ca">Enviar (català)</button>
      <button type="button" class="btn-petit btn-secondari js-send"
              data-invoice-id="${row.id}" data-lang="es">Enviar (castellà)</button>
      <button type="button" class="btn-petit btn-secondari js-send"
              data-invoice-id="${row.id}" data-lang="en">Enviar (anglès)</button>
      <button type="button" class="btn-petit btn-secondari js-send"
              data-invoice-id="${row.id}" data-lang="it">Enviar (italià)</button>
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
    // Descargar PDF
    const btnPdf = target.closest<HTMLButtonElement>('.js-pdf');
    if (btnPdf) {
      const idStr = btnPdf.dataset.invoiceId;
      const lang = (btnPdf.dataset.lang || '').toLowerCase() as 'ca' | 'es' | 'en' | 'it';
      const fname = btnPdf.dataset.fileName || undefined;
      if (!idStr || !['ca', 'es', 'en', 'it'].includes(lang)) return;
      const idNum = Number(idStr);
      if (!Number.isInteger(idNum) || idNum <= 0) return;
      void generatePDF(idNum, lang, fname, btnPdf);
      return;
    }

    // Enviar por email
    const btnSend = target.closest<HTMLButtonElement>('.js-send');
    if (btnSend) {
      const idStr = btnSend.dataset.invoiceId;
      const lang = (btnSend.dataset.lang || '').toLowerCase() as 'ca' | 'es' | 'en' | 'it';
      if (!idStr || !['ca', 'es', 'en', 'it'].includes(lang)) return;
      const idNum = Number(idStr);
      if (!Number.isInteger(idNum) || idNum <= 0) return;
      void sendInvoiceEmail(idNum, lang, btnSend);
      return;
    }
  });
}
