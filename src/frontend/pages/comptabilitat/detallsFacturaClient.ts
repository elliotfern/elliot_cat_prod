// invoice-page.ts

import { API_BASE, DOMAIN_WEB } from '../../utils/urls';

type JSONObject = Record<string, unknown>;

// === Config ===
const API_URLS = {
  INVOICE_BY_ID: (id: string | number) => `${API_BASE}/comptabilitat/get/facturaCompleta?id=${id}`,
};

// === Tipos ===
interface ApiResponse<T> {
  status: 'success' | 'error' | string;
  message?: string;
  errors?: unknown[];
  data: T;
}

interface Invoice {
  id: number;
  client_id: number;
  concepte: string;
  data_factura: string;
  yearInvoice: number;
  any: string;
  data_venciment: string | null;
  base_imposable: number;
  despeses_extra: number;
  total_factura: number;
  import_iva: number;
  tipus_iva: number;
  estat: number | string;
  metode_pagament: number | string;
  ivaPercen: number;
  estatNom?: string;
  tipusNom?: string;
  metodeNotes?: string;
  clientNom: string | null;
  clientCognoms: string | null;
  clientEmpresa: string | null;
  numero_factura: string;
}

interface InvoiceLine {
  id: number;
  factura_id: number;
  producte: string;
  descripcio: string | null;
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

async function fetchApiData<T>(url: string, init?: RequestInit): Promise<T> {
  const json = await fetchJSON<ApiResponse<T>>(url, init);
  if (json.status !== 'success') throw new Error(json.message || 'Error API');
  return json.data;
}

// === Render ===
function renderInvoiceHeader(container: HTMLElement, inv: Invoice): void {
  const client = inv.clientEmpresa?.trim() ? `<strong>${escHtml(inv.clientEmpresa)}</strong>` : [inv.clientNom, inv.clientCognoms].filter(Boolean).map(escHtml).join(' ') || '—';

  const editUrl = `${DOMAIN_WEB}/gestio/comptabilitat/modifica-factura/${inv.id}`;

  container.innerHTML = `
    <div class="invoice-header">
      <h2 class="mb-1">Factura #${escHtml(String(inv.numero_factura))}</small></h2>
      <p class="m-0"><strong>Concepte:</strong> ${escHtml(inv.concepte || '—')}</p>
      <p class="m-0"><strong>Client:</strong> ${client}</p>
      <div class="row mt-2">
        <div class="col">
          <span class="d-block"><strong>Data:</strong> ${formatDate(inv.data_factura)}</span>
          <span class="d-block"><strong>Venciment:</strong> ${formatDate(inv.data_venciment)}</span>
        </div>
        <div class="col">
          <span class="d-block"><strong>Estat:</strong> ${escHtml(inv.estatNom || String(inv.estat) || '—')}</span>
          <span class="d-block"><strong>Pagament:</strong> ${escHtml(inv.tipusNom || String(inv.metode_pagament) || '—')}</span>
        </div>
        <div class="col">
          <span class="d-block"><strong>IVA %:</strong> ${escHtml(String(inv.ivaPercen ?? '—'))}%</span>
        </div>
      </div>
    </div>

      <!-- Botón editar -->
      <div>
        <a href="${editUrl}" class="btn btn-primary">
          ✏️ Modificar factura
        </a>
      </div>

    </div>
  `;
}

function renderInvoiceAmounts(container: HTMLElement, inv: Invoice): void {
  const vatAmount = typeof inv.import_iva === 'number' ? inv.import_iva : 0;

  container.innerHTML = `
    <div class="card p-3 shadow-sm">
      <div class="row">
        <div class="col">
          <div><strong>Subtotal</strong></div>
          <div>${formatEUR(inv.base_imposable)}</div>
        </div>
        <div class="col">
          <div><strong>Despeses extres</strong></div>
          <div>${formatEUR(inv.despeses_extra)}</div>
        </div>
        <div class="col">
          <div><strong>IVA</strong></div>
          <div>${formatEUR(vatAmount)}</div>
        </div>
        <div class="col">
          <div><strong>Total</strong></div>
          <div class="fs-5">${formatEUR(inv.total_factura)}</div>
        </div>
      </div>
    </div>
  `;
}

function renderProducts(container: HTMLElement, invoiceId: number, lines: InvoiceLine[]): void {
  if (!lines.length) {
    container.innerHTML = `
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
      <td class="align-middle">${escHtml(l.descripcio || '')}</td>
      <td class="align-middle text-end">${formatEUR(l.preu)}</td>
    </tr>
  `
    )
    .join('');

  container.innerHTML = `
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle">
        <thead>
          <tr>
            <th style="width:40%">Producte</th>
            <th style="width:40%">Descripció</th>
            <th class="text-end" style="width:10%">Preu</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </div>
  `;
}

// === API Calls ===
async function getInvoice(id: string | number): Promise<Invoice> {
  const data = await fetchApiData<{ factura: Invoice; productes: InvoiceLine[] }>(API_URLS.INVOICE_BY_ID(id));
  return data.factura;
}

async function getInvoiceLines(id: string | number): Promise<InvoiceLine[]> {
  const data = await fetchApiData<{ factura: Invoice; productes: InvoiceLine[] }>(API_URLS.INVOICE_BY_ID(id));
  return data.productes ?? [];
}

// === Init ===
export async function detallsFacturaClients(rootSelector = '#invoiceRoot'): Promise<void> {
  const root = document.querySelector<HTMLElement>(rootSelector);
  if (!root) return;

  const invoiceId = root.dataset.invoiceId;
  if (!invoiceId) return;

  const headerEl = document.getElementById('invoiceHeader');
  const amountsEl = document.getElementById('invoiceAmounts');
  const productsEl = document.getElementById('invoiceProducts');

  if (!headerEl || !amountsEl || !productsEl) return;

  // Loading placeholders
  headerEl.innerHTML = `<div class="placeholder-glow"><span class="placeholder col-6"></span></div>`;
  amountsEl.innerHTML = `<div class="placeholder-glow"><span class="placeholder col-3"></span></div>`;
  productsEl.innerHTML = `<div class="placeholder-glow"><span class="placeholder col-12"></span></div>`;

  try {
    const invoice = await getInvoice(invoiceId);
    renderInvoiceHeader(headerEl, invoice);
    renderInvoiceAmounts(amountsEl, invoice);

    try {
      const lines = await getInvoiceLines(invoiceId);
      renderProducts(productsEl, invoice.id, lines);
    } catch {
      productsEl.innerHTML = `
        <hr>
        <div class="alert alert-warning">No s'han pogut carregar les línies de producte en aquest moment.</div>
      `;
    }
  } catch (err) {
    headerEl.innerHTML = '';
    amountsEl.innerHTML = '';
    productsEl.innerHTML = `
      <div class="alert alert-danger">S'ha produït un error en carregar la factura. Torna-ho a intentar més tard.</div>
    `;
  }
}
