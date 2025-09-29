// invoice-page.ts

import { API_BASE, DOMAIN_WEB } from '../../utils/urls';

type JSONObject = Record<string, unknown>;

// === Config ===
const API_URLS = {
  INVOICE_BY_ID: (id: string | number) => `${API_BASE}/comptabilitat/get/facturaId?id=${id}`, // GET
  INVOICE_LINES: (id: string | number) => `${API_BASE}/comptabilitat/get/detallsFacturaClientId?id=${id}`, // GET
  DELETE_LINE: (id: string | number) => `${API_BASE}/comptabilitat/${id}`, // DELETE
};

// URL para editar línea (ajusta a tu patrón real)
const MOD_URLS = {
  EDIT_LINE: (invoiceId: string | number, lineId: string | number) => `${DOMAIN_WEB}/gestio/comptabilitat/modifica-producte-factura/${lineId}`,
};

// URL alta de producto (ajusta si prefieres path param)
const NEW_PRODUCT_URL = `${DOMAIN_WEB}/gestio/comptabilitat/nou-producte-factura`;

// === Tipos ===
interface ApiResponse<T> {
  status: 'success' | 'error' | string;
  message?: string;
  errors?: unknown[];
  data: T;
}

interface Invoice {
  id: number;
  idUser: number;
  facConcepte: string;
  facData: string;
  yearInvoice: number;
  any: string;
  facDueDate: string | null;
  facSubtotal: number;
  facFees: number;
  facTotal: number;
  facVAT: number; // importe del IVA (en tu ejemplo: 0)
  facIva: number; // no lo uso para importe
  facEstat: number | string;
  facPaymentType: number | string;
  ivaPercen: number; // porcentaje %
  estat?: string; // “Completed” en tu ejemplo
  tipusNom?: string; // nombre del método de pago
  clientNom: string | null;
  clientCognoms: string | null;
  clientEmpresa: string | null;
}

interface InvoiceLine {
  id: number;
  factura_id: number;
  producte: string;
  notes: string | null;
  preu: number;
}

// === Helpers ===
const formatEUR = (n: number | string, locale = 'ca-ES'): string => {
  const num = typeof n === 'string' ? Number(n) : n;
  if (!isFinite(num)) return '—';
  return new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(num);
};

const formatDate = (d?: string | null, locale = 'ca-ES'): string => {
  if (!d) return '—';
  const dt = new Date(d);
  if (Number.isNaN(dt.getTime())) return d;
  return new Intl.DateTimeFormat(locale, { year: 'numeric', month: 'short', day: '2-digit' }).format(dt);
};

function escHtml(s: unknown): string {
  return String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

async function fetchJSON<T = JSONObject>(url: string, init?: RequestInit): Promise<T> {
  const res = await fetch(url, {
    headers: { Accept: 'application/json' },
    credentials: 'include',
    ...init,
  });
  if (!res.ok) {
    const text = await res.text().catch(() => '');
    throw new Error(`Fetch error ${res.status} ${res.statusText}: ${text || url}`);
  }
  return res.json() as Promise<T>;
}

// Desenvuelve {status, data}
async function fetchApiData<T>(url: string, init?: RequestInit): Promise<T> {
  const json = await fetchJSON<ApiResponse<T>>(url, init);
  if (json.status !== 'success') {
    throw new Error(json.message || 'Error API');
  }
  return json.data;
}

// === Render: Capçalera i imports ===
function renderInvoiceHeader(container: HTMLElement, inv: Invoice): void {
  const client = inv.clientEmpresa && inv.clientEmpresa.trim() ? `<strong>${escHtml(inv.clientEmpresa)}</strong>` : [inv.clientNom, inv.clientCognoms].filter(Boolean).map(escHtml).join(' ') || '—';

  container.innerHTML = `
    <div class="invoice-header">
      <h2 class="mb-1">Factura #${escHtml(inv.id)} <small class="text-muted">(${escHtml(inv.any)})</small></h2>
      <p class="m-0"><strong>Concepte:</strong> ${escHtml(inv.facConcepte || '—')}</p>
      <p class="m-0"><strong>Client:</strong> ${client}</p>
      <div class="row mt-2">
        <div class="col">
          <span class="d-block"><strong>Data:</strong> ${formatDate(inv.facData)}</span>
          <span class="d-block"><strong>Venciment:</strong> ${formatDate(inv.facDueDate)}</span>
        </div>
        <div class="col">
          <span class="d-block"><strong>Estat:</strong> ${escHtml(inv.estat || String(inv.facEstat) || '—')}</span>
          <span class="d-block"><strong>Pagament:</strong> ${escHtml(inv.tipusNom || String(inv.facPaymentType) || '—')}</span>
        </div>
        <div class="col">
          <span class="d-block"><strong>IVA %:</strong> ${escHtml(String(inv.ivaPercen ?? '—'))}%</span>
        </div>
      </div>
    </div>
  `;
}

function renderInvoiceAmounts(container: HTMLElement, inv: Invoice): void {
  // Usa facVAT si está; si no, calcula por porcentaje
  const vatAmount = typeof inv.facVAT === 'number' && !Number.isNaN(inv.facVAT) ? inv.facVAT : typeof inv.ivaPercen === 'number' && !Number.isNaN(inv.ivaPercen) ? inv.facSubtotal * (inv.ivaPercen / 100) : 0;

  container.innerHTML = `
    <div class="card p-3 shadow-sm">
      <div class="row">
        <div class="col">
          <div><strong>Subtotal</strong></div>
          <div>${formatEUR(inv.facSubtotal)}</div>
        </div>
        <div class="col">
          <div><strong>Taxes/Fees</strong></div>
          <div>${formatEUR(inv.facFees)}</div>
        </div>
        <div class="col">
          <div><strong>IVA</strong></div>
          <div>${formatEUR(vatAmount)}</div>
        </div>
        <div class="col">
          <div><strong>Total</strong></div>
          <div class="fs-5">${formatEUR(inv.facTotal)}</div>
        </div>
      </div>
    </div>
  `;
}

// === Render: Taula de productes ===
function renderProducts(container: HTMLElement, invoiceId: number, lines: InvoiceLine[]): void {
  const addBtnHTML = `
    <hr>
    <div class="mb-3" style="margin-top:35px">
      <a class="button btn-gran btn-secondari"
         href="${NEW_PRODUCT_URL}">Afegir producte</a>
    </div>
  `;

  if (!lines.length) {
    container.innerHTML = `
      ${addBtnHTML}
      <div class="alert alert-info">Aquesta factura no té línies de producte.</div>
      <div class="table-responsive"></div>
    `;
    return;
  }

  const rows = lines
    .map(
      (l) => `
    <tr data-line-id="${escHtml(l.id)}">
      <td class="align-middle">${escHtml(l.producte)}</td>
      <td class="align-middle">${escHtml(l.notes || '')}</td>
      <td class="align-middle text-end">${formatEUR(l.preu)}</td>
      <td class="align-middle text-end">
        <a class="btn btn-sm btn-outline-primary me-2 js-edit"
           href="${escHtml(MOD_URLS.EDIT_LINE(invoiceId, l.id))}"
           rel="noopener">Modifica</a>
        <button class="btn btn-sm btn-outline-danger js-delete" type="button">Eliminar</button>
      </td>
    </tr>
  `
    )
    .join('');

  container.innerHTML = `
    ${addBtnHTML}
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle">
        <thead>
          <tr>
            <th style="width:40%">Producte</th>
            <th style="width:40%">Notes</th>
            <th class="text-end" style="width:10%">Preu</th>
            <th class="text-end" style="width:10%">Accions</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </div>
  `;

  const tbody = container.querySelector('tbody');
  if (!tbody) return;

  tbody.addEventListener('click', async (ev) => {
    const target = ev.target as HTMLElement | null;
    const btn = target?.closest<HTMLButtonElement>('.js-delete');
    if (!btn) return;

    const tr = btn.closest<HTMLTableRowElement>('tr');
    const lineId = tr?.getAttribute('data-line-id');
    if (!lineId) return;

    if (!window.confirm('Vols eliminar aquesta línia? Aquesta acció és irreversible.')) return;

    try {
      btn.disabled = true;
      btn.textContent = 'Eliminant...';
      await deleteLine(lineId);
      tr?.remove();

      if (!container.querySelector('tbody tr')) {
        // si se borran todas, mostramos estado vacío con el botón
        container.innerHTML = `
          ${addBtnHTML}
          <div class="alert alert-info">Aquesta factura no té línies de producte.</div>
          <div class="table-responsive"></div>
        `;
      }
    } catch (e) {
      window.alert('No s’ha pogut eliminar la línia. Torna-ho a intentar.');
      console.error(e);
      btn.disabled = false;
      btn.textContent = 'Eliminar';
    }
  });
}

// === API calls ===
async function getInvoice(id: string | number): Promise<Invoice> {
  return fetchApiData<Invoice>(API_URLS.INVOICE_BY_ID(id));
}

async function getInvoiceLines(id: string | number): Promise<InvoiceLine[]> {
  const data = await fetchApiData<InvoiceLine | InvoiceLine[]>(API_URLS.INVOICE_LINES(id));
  return Array.isArray(data) ? data : data ? [data] : [];
}

async function deleteLine(lineId: string | number): Promise<void> {
  await fetchApiData<unknown>(API_URLS.DELETE_LINE(lineId), { method: 'DELETE' });
}

// === Init ===
export async function detallsFacturaClients(rootSelector = '#invoiceRoot'): Promise<void> {
  const root = document.querySelector<HTMLElement>(rootSelector);
  if (!root) {
    console.warn(`[detallsFacturaClients] No existe ${rootSelector}`);
    return;
  }

  const invoiceId = root.dataset.invoiceId;
  if (!invoiceId) {
    console.error('[detallsFacturaClients] Falta data-invoice-id en #invoiceRoot');
    return;
  }

  const headerEl = document.getElementById('invoiceHeader');
  const amountsEl = document.getElementById('invoiceAmounts');
  const productsEl = document.getElementById('invoiceProducts');

  if (!headerEl || !amountsEl || !productsEl) {
    console.error('[detallsFacturaClients] Falten contenedors #invoiceHeader/#invoiceAmounts/#invoiceProducts');
    return;
  }

  headerEl.innerHTML = `<div class="placeholder-glow"><span class="placeholder col-6"></span></div>`;
  amountsEl.innerHTML = `<div class="placeholder-glow"><span class="placeholder col-3"></span></div>`;
  productsEl.innerHTML = `<div class="placeholder-glow"><span class="placeholder col-12"></span></div>`;

  try {
    const [invoice, lines] = await Promise.all([getInvoice(invoiceId), getInvoiceLines(invoiceId)]);

    renderInvoiceHeader(headerEl, invoice);
    renderInvoiceAmounts(amountsEl, invoice);
    renderProducts(productsEl, invoice.id, lines);
  } catch (err) {
    console.error(err);
    headerEl.innerHTML = '';
    amountsEl.innerHTML = '';
    productsEl.innerHTML = `
      <div class="alert alert-danger">
        S'ha produït un error en carregar la factura. Torna-ho a intentar més tard.
      </div>
    `;
  }
}
