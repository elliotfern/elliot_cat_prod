import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { formatData } from '../../utils/formataData';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Factura } from '../../types/Factura';
import { API_URLS } from '../../utils/apiUrls';
import { api } from '../../core/api/client';
import { formatEuro } from '../../utils/locales/formatEuro';

// Generador PDF por idioma
async function generatePDF(invoiceId: string, lang: 'ca' | 'es' | 'en' | 'it', fileName?: string, btn?: HTMLButtonElement | null) {
  const prevLabel = btn?.textContent;

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Generant...';
  }

  try {
    const endpoint = API_URLS.GET.INVOICE_PDF(invoiceId, lang);

    const response = await fetch(endpoint, {
      credentials: 'include',
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const blob = await response.blob();

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

// Enviar factura por email
async function sendInvoiceEmail(invoiceId: string, lang: 'ca' | 'es' | 'en' | 'it', btn?: HTMLButtonElement | null) {
  const prevLabel = btn?.textContent;

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Enviant…';
  }

  try {
    const endpoint = API_URLS.POST.ENVIAR_FACTURA_EMAIL(invoiceId, lang);

    await api.post(endpoint);

    alert('Enviat correctament ✅');
  } catch (e) {
    console.error('Error enviant el correu:', e);
    alert("No s'ha pogut enviar el correu.");
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = prevLabel || 'Enviar';
    }
  }
}

// Emissors
const EMISSORS: Record<string, string> = {
  '019e3ebaf71370c2860a40a79fb5ad7b': 'Hispano Atlantic Consulting Ltd (juliol 2017 - octubre 2022)',

  '019e3ebaf71370c2860a40a7a078beb4': 'Autònom Irlanda (1 novembre 2022 - 29 març 2026)',

  '019e3ebaf71370c2860a40a7a15db129': 'Partita Iva Itàlia (30 març 2026 - )',
};

export function renderTitolEmissor(emissorId: string) {
  const container = document.getElementById('titolTipusFactura');

  if (!container) return;

  const titol = EMISSORS[emissorId] || 'Emissor desconegut';

  container.innerHTML = `<h3>${titol}</h3>`;
}

export async function taulaFacturacioClients(id: string) {
  const isAdmin = await getIsAdmin();

  const columns: TaulaDinamica<Factura>[] = [
    // NUMERO FACTURA
    {
      header: 'Num',
      field: 'numero_factura',
      render: (_: unknown, row: Factura) =>
        `<a
          id="${row.id}"
          href="/gestio/comptabilitat/fitxa-factura-client/${row.id}">
          ${row.numero_factura}
        </a>`,
    },

    // EMPRESA / CLIENT
    {
      header: 'Empresa',
      field: 'empresa',
      render: (_: unknown, row: Factura) => (row.empresa ? row.empresa : `${row.nom} ${row.cognoms}`),
    },

    // DATA
    {
      header: 'Data factura',
      field: 'data_factura',
      render: (_: unknown, row: Factura) => formatData(row.data_factura),
    },

    // CONCEPTE
    {
      header: 'Concepte',
      field: 'concepte',
    },

    // TOTAL
    {
      header: 'Total',
      field: 'total_factura',
      render: (_: unknown, row: Factura) => `${formatEuro(row.total_factura)}`,
    },

    // ESTAT
    {
      header: 'Estat',
      field: 'estat',
      render: (_: unknown, row: Factura) =>
        `<span class="badge bg-primary">
          ${row.estat}
        </span>`,
    },

    // PDF
    {
      header: 'PDF',
      field: 'id',
      render: (_: unknown, row: Factura) => `
        <div
          class="btn-group"
          role="group"
          aria-label="Generar PDF">

          <button
            type="button"
            class="btn btn-sm btn-secondary js-pdf"
            data-invoice-id="${row.id}"
            data-lang="ca">
            CA
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary js-pdf"
            data-invoice-id="${row.id}"
            data-lang="es">
            ES
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary js-pdf"
            data-invoice-id="${row.id}"
            data-lang="en">
            EN
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary js-pdf"
            data-invoice-id="${row.id}"
            data-lang="it">
            IT
          </button>

        </div>
      `,
    },

    // EMAIL
    {
      header: 'Enviar email',
      field: 'id',
      render: (_: unknown, row: Factura) => `
        <div
          class="btn-group"
          role="group"
          aria-label="Enviar email">

          <button
            type="button"
            class="btn btn-sm btn-secondary js-send"
            data-invoice-id="${row.id}"
            data-lang="ca">
            CA
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary js-send"
            data-invoice-id="${row.id}"
            data-lang="es">
            ES
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary js-send"
            data-invoice-id="${row.id}"
            data-lang="en">
            EN
          </button>

          <button
            type="button"
            class="btn btn-sm btn-secondary js-send"
            data-invoice-id="${row.id}"
            data-lang="it">
            IT
          </button>

        </div>
      `,
    },
  ];

  if (isAdmin) {
    const columnaAccions: TaulaDinamica<Factura> = {
      header: 'Accions',
      field: 'id',
      render: (_: unknown, row: Factura): string => `
      <a
        href="/gestio/comptabilitat/modifica-factura/${row.id}"
        class="btn btn-sm btn-warning">
        Modifica
      </a>
    `,
    };

    columns.push(columnaAccions);
  }

  // TABLA
  renderDynamicTable({
    url: `${API_URLS.GET.FACTURACIO_CLIENTS}?id=${id}`,
    containerId: 'taulaLlistatFactures',
    columns,
    filterKeys: ['clientEmpresa', 'clientCognoms'],
    filterByField: 'any',
  });

  // TITULO EMISOR
  renderTitolEmissor(id);

  // EVENTOS
  const container = document.getElementById('taulaLlistatFactures');

  container?.addEventListener('click', (ev) => {
    const target = ev.target as HTMLElement;

    // PDF
    const btnPdf = target.closest<HTMLButtonElement>('.js-pdf');

    if (btnPdf) {
      const invoiceId = btnPdf.dataset.invoiceId;

      const lang = btnPdf.dataset.lang as 'ca' | 'es' | 'en' | 'it';

      if (!invoiceId || !lang) return;

      void generatePDF(invoiceId, lang, undefined, btnPdf);

      return;
    }

    // EMAIL
    const btnSend = target.closest<HTMLButtonElement>('.js-send');

    if (btnSend) {
      const invoiceId = btnSend.dataset.invoiceId;

      const lang = btnSend.dataset.lang as 'ca' | 'es' | 'en' | 'it';

      if (!invoiceId || !lang) return;

      void sendInvoiceEmail(invoiceId, lang, btnSend);
    }
  });
}
