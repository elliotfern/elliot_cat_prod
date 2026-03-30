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

const EMISSORS: Record<number, string> = {
  1: 'Hispano Atlantic Consulting Ltd (juliol 2017 - octubre 2022)',
  2: 'Autònom Irlanda (1 novembre 2022 - 29 març 2026)',
  3: 'Partita Iva Itàlia (30 març 2026 - )',
};

export function renderTitolEmissor(emissorId: number) {
  const container = document.getElementById('titolTipusFactura');
  if (!container) return;

  const titol = EMISSORS[emissorId] || 'Emissor desconegut';

  container.innerHTML = `<h3>${titol}</h3>`;
}

export async function taulaFacturacioClients(emissorId: number) {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Factura>[] = [
    {
      header: 'Num',
      field: 'numero_factura',
      render: (_: unknown, row: Factura) =>
        `<a id="${row.id}" href="${DOMAIN_WEB}/gestio/comptabilitat/fitxa-factura-client/${row.id}">
          ${row.numero_factura}
        </a>`,
    },
    {
      header: 'Empresa',
      field: 'clientEmpresa',
      render: (_: unknown, row: Factura) => `${row.clientEmpresa ? row.clientEmpresa : `${row.clientNom} ${row.clientCognoms}`}`,
    },
    {
      header: 'Data factura',
      field: 'data_factura',
      render: (_: unknown, row: Factura) => {
        return formatData(row.data_factura);
      },
    },
    {
      header: 'Concepte',
      field: 'concepte',
    },
    {
      header: 'Total',
      field: 'total_factura',
      render: (_: unknown, row: Factura) => `${row.total_factura}€`,
    },
    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: Factura) => `<button class="btn-petit btn-primari">${row.estat}</button>`,
    },
    {
      header: 'PDF',
      field: 'id',
      render: (_: unknown, row: Factura) => `
        <div class="btn-group separat">
          <button class="btn-petit btn-secondari js-pdf" data-invoice-id="${row.id}" data-lang="ca">PDF (CA)</button>
          <button class="btn-petit btn-secondari js-pdf" data-invoice-id="${row.id}" data-lang="es">PDF (ES)</button>
          <button class="btn-petit btn-secondari js-pdf" data-invoice-id="${row.id}" data-lang="en">PDF (EN)</button>
          <button class="btn-petit btn-secondari js-pdf" data-invoice-id="${row.id}" data-lang="it">PDF (IT)</button>
        </div>
      `,
    },
    {
      header: 'Enviar email',
      field: 'id',
      render: (_: unknown, row: Factura) => `
        <div class="btn-group separat">
          <button class="btn-petit btn-secondari js-send" data-invoice-id="${row.id}" data-lang="ca">CA</button>
          <button class="btn-petit btn-secondari js-send" data-invoice-id="${row.id}" data-lang="es">ES</button>
          <button class="btn-petit btn-secondari js-send" data-invoice-id="${row.id}" data-lang="en">EN</button>
          <button class="btn-petit btn-secondari js-send" data-invoice-id="${row.id}" data-lang="it">IT</button>
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
    url: API_URLS.GET.FACTURACIO_CLIENTS(emissorId),
    containerId: 'taulaLlistatFactures',
    columns,
    filterKeys: ['clientEmpresa', 'clientCognoms'],
    filterByField: 'any',
  });

  const container = document.getElementById('taulaLlistatFactures');
  renderTitolEmissor(emissorId);

  container?.addEventListener('click', (ev) => {
    const target = ev.target as HTMLElement;

    const btnPdf = target.closest<HTMLButtonElement>('.js-pdf');
    if (btnPdf) {
      const idNum = Number(btnPdf.dataset.invoiceId);
      const lang = btnPdf.dataset.lang as 'ca' | 'es' | 'en' | 'it';
      if (!idNum || !lang) return;
      void generatePDF(idNum, lang);
      return;
    }

    const btnSend = target.closest<HTMLButtonElement>('.js-send');
    if (btnSend) {
      const idNum = Number(btnSend.dataset.invoiceId);
      const lang = btnSend.dataset.lang as 'ca' | 'es' | 'en' | 'it';
      if (!idNum || !lang) return;
      void sendInvoiceEmail(idNum, lang, btnSend);
      return;
    }
  });
}
