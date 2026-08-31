import { renderDynamicTable } from '../../components/renderTaula/taulaRender';
import { getIsAdmin } from '../../services/auth/isAdmin';
import { FacturaClient } from '../../types/Client';
import { TaulaDinamica } from '../../types/TaulaDinamica';
import { Button } from '../../Presentation/Components/Button/Button';
import { API_URLS } from '../../utils/apiUrls';
import { INTRANET_URLS } from '../../utils/IntranetUrls';
import { formatEuro } from '../../utils/locales/formatEuro';

// Generador PDF por idioma
async function generatePDF(invoiceId: string, lang: 'ca', fileName?: string, btn?: HTMLButtonElement | null) {
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

export async function renderClientFactures(clientId: string) {
  const isAdmin = await getIsAdmin();
  const columns: TaulaDinamica<FacturaClient>[] = [
    {
      header: 'Factura',
      field: 'numero_factura',

      render: (_: unknown, row: FacturaClient) => `
        <strong><a href="/gestio/comptabilitat/fitxa-factura-client/${row.id}">${row.numero_factura}</a></strong>
      `,
    },

    {
      header: 'Concepte',
      field: 'concepte',

      render: (_: unknown, row: FacturaClient) => row.concepte ?? '',
    },

    {
      header: 'Data factura',
      field: 'data_factura',

      render: (_: unknown, row: FacturaClient) => (row.data_factura ? new Date(row.data_factura).toLocaleDateString('ca-ES') : ''),
    },

    {
      header: 'Import',
      field: 'total_factura',

      render: (_: unknown, row: FacturaClient) => `
        <strong>
          ${Number(row.total_factura).toFixed(2)} €
        </strong>
      `,
    },

    {
      header: 'Estat',
      field: 'estat',

      render: (_: unknown, row: FacturaClient) => `
        <span class="badge bg-info">
          ${row.estat ?? ''}
        </span>
      `,
    },

    // PDF
    {
      header: 'PDF',
      field: 'id',
      render: (_: unknown, row: FacturaClient) => `
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
            </div>
          `,
    },
  ];

  if (isAdmin) {
    columns.push({
      header: '',
      field: 'id',
      render: (_, { id }) => Button.edit('Modifica', INTRANET_URLS.COMPTABILITAT.FACTURA_MODIFICA_ID(id)),
    });
  }

  renderDynamicTable({
    url: `comptabilitat/get/facturesClientId?id=${clientId}`,
    containerId: 'clientFactures',
    columns,
    filterKeys: ['numero_factura', 'concepte'],
    dataKey: 'factures',
    rowsPerPage: 9999,

    renderHeader: ({ raw }: any) => {
      const total = raw?.totals?.total_facturat ?? 0;

      return `
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="h4 mb-0">Factures client</h2>
          <small class="text-muted">
            Total facturat acumulat
          </small>
        </div>

        <div class="text-end">
          <div class="fs-4 fw-bold text-success">
            ${formatEuro(total)}
            </div>
            <small class="text-muted">
            Import total facturat
          </small>
          
        </div>
      </div>
    `;
    },
  });

  // EVENTOS
  const container = document.getElementById('clientFactures');

  container?.addEventListener('click', (ev) => {
    const target = ev.target as HTMLElement;

    // PDF
    const btnPdf = target.closest<HTMLButtonElement>('.js-pdf');

    if (btnPdf) {
      const invoiceId = btnPdf.dataset.invoiceId;

      const lang = btnPdf.dataset.lang as 'ca';

      if (!invoiceId || !lang) return;

      void generatePDF(invoiceId, lang, undefined, btnPdf);

      return;
    }
  });
}
