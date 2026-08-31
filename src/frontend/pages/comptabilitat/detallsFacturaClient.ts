import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { DOMAIN_WEB } from '../../utils/urls';

// ============================================================
// CONFIG
// ============================================================

const API_URLS = {
  INVOICE_BY_ID: 'comptabilitat/get/facturaCompleta',
};

// ============================================================
// INTERFACES
// ============================================================

interface Invoice {
  id: number;
  numero_factura: string;

  emissor_id: string | null;
  client_id: string | null;

  concepte: string | null;

  data_factura: string | null;
  yearInvoice: number | null;
  any: string | null;
  data_venciment: string | null;

  base_imposable: number;
  despeses_extra: number;
  total_factura: number;
  import_iva: number;

  tipus_iva: number;
  ivaPercen: number;

  estat_id: number | null;
  estat: string | number | null;

  metode_pagament: number | null;
  tipus: string | null;

  notes: string | null;

  projecte_id: number | null;

  arxiu_url: string | null;

  recurrent: number | boolean;
  frequencia: string | null;

  // ==========================================================
  // CLIENT / CONTACTE
  // ==========================================================

  nom: string | null;
  cognoms: string | null;
  empresa: string | null;
  email: string | null;
  web: string | null;
  nif: string | null;
  adreca: string | null;
  cp: string | null;
  ciutat: string | null;
  provincia: string | null;
  pais: string | null;

  // ==========================================================
  // EMISSOR
  // ==========================================================

  numero_iva: string | null;
  telefonEmissor: string | null;
  nomEmissor: string | null;
  emailEmissor: string | null;
  nifEmissor: string | null;
  adrecaEmissor: string | null;
  paisEmissor: string | null;
}

interface InvoiceLine {
  id: number;
  factura_id: string | number;
  producte_id: number;
  producte: string | null;
  descripcio: string | null;
  preu: number;
}

// ============================================================
// HELPERS
// ============================================================

const formatEUR = (value: number | string | null | undefined, locale = 'ca-ES'): string => {
  if (value === null || value === undefined || value === '') {
    return '—';
  }

  const num = typeof value === 'string' ? Number(value) : value;

  if (!Number.isFinite(num)) {
    return '—';
  }

  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: 'EUR',
  }).format(num);
};

const formatDate = (date: string | null | undefined, locale = 'ca-ES'): string => {
  if (!date) {
    return '—';
  }

  // Evitamos problemas de timezone con YYYY-MM-DD
  const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(date);

  if (match) {
    const [, year, month, day] = match;

    return new Intl.DateTimeFormat(locale, {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    }).format(new Date(Number(year), Number(month) - 1, Number(day)));
  }

  const dt = new Date(date);

  if (Number.isNaN(dt.getTime())) {
    return date;
  }

  return new Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  }).format(dt);
};

function escHtml(value: unknown): string {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function valueOrDash(value: unknown): string {
  if (value === null || value === undefined || String(value).trim() === '') {
    return '—';
  }

  return escHtml(value);
}

// ============================================================
// API
// ============================================================

async function getInvoiceData(id: string): Promise<{
  factura: Invoice;
  productes: InvoiceLine[];
}> {
  return api.get<{
    factura: Invoice;
    productes: InvoiceLine[];
  }>(API_URLS.INVOICE_BY_ID, {
    id,
  });
}

// ============================================================
// HEADER FACTURA
// ============================================================

function renderInvoiceHeader(container: HTMLElement, inv: Invoice): void {
  const clientNomComplet = [inv.nom, inv.cognoms].filter((value) => value && value.trim() !== '').join(' ');

  const clientPrincipal = inv.empresa?.trim() || clientNomComplet || '—';

  const editUrl = `${DOMAIN_WEB}/gestio/comptabilitat/modifica-factura/${inv.id}`;

  container.innerHTML = `
    <div class="card shadow-sm mb-4">
      <div class="card-body">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">

          <div>
            <h2 class="mb-1">
              Factura #${escHtml(inv.numero_factura)}
            </h2>

            <p class="mb-1">
              <strong>Concepte:</strong>
              ${valueOrDash(inv.concepte)}
            </p>

            <p class="mb-0">
              <strong>Client:</strong>
              ${escHtml(clientPrincipal)}
            </p>
          </div>

          <div class="flex-shrink-0">
            <a
              href="${escHtml(editUrl)}"
              class="btn btn-primary"
            >
              ✏️ Modificar factura
            </a>
          </div>

        </div>

        <hr>

        <div class="row g-3">

          <div class="col-12 col-md-6 col-xl-3">
            <div class="small text-muted">
              Data de factura
            </div>
            <div class="fw-semibold">
              ${formatDate(inv.data_factura)}
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="small text-muted">
              Data de venciment
            </div>
            <div class="fw-semibold">
              ${formatDate(inv.data_venciment)}
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="small text-muted">
              Estat
            </div>
            <div class="fw-semibold">
              ${valueOrDash(inv.estat)}
            </div>
          </div>

          <div class="col-12 col-md-6 col-xl-3">
            <div class="small text-muted">
              Mètode de pagament
            </div>
            <div class="fw-semibold">
              ${valueOrDash(inv.tipus)}
            </div>
          </div>

        </div>

      </div>
    </div>
  `;
}

// ============================================================
// CLIENT + EMISSOR
// ============================================================

function renderParties(container: HTMLElement, inv: Invoice): void {
  const clientNomComplet = [inv.nom, inv.cognoms].filter((value) => value && value.trim() !== '').join(' ');

  container.innerHTML = `
    <div class="row g-4 mb-4">

      <!-- CLIENT -->
      <div class="col-12 col-lg-6">

        <div class="card h-100 shadow-sm">

          <div class="card-header fw-semibold">
            Client
          </div>

          <div class="card-body">

            <div class="mb-2">
              <strong>Empresa:</strong>
              ${valueOrDash(inv.empresa)}
            </div>

            <div class="mb-2">
              <strong>Nom:</strong>
              ${valueOrDash(clientNomComplet)}
            </div>

            <div class="mb-2">
              <strong>NIF:</strong>
              ${valueOrDash(inv.nif)}
            </div>

            <div class="mb-2">
              <strong>Email:</strong>
              ${valueOrDash(inv.email)}
            </div>

            <div class="mb-2">
              <strong>Web:</strong>
              ${
                inv.web
                  ? `
                    <a
                      href="${escHtml(inv.web)}"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      ${escHtml(inv.web)}
                    </a>
                  `
                  : '—'
              }
            </div>

            <div class="mb-2">
              <strong>Adreça:</strong>
              ${valueOrDash(inv.adreca)}
            </div>

            <div class="mb-2">
              <strong>CP:</strong>
              ${valueOrDash(inv.cp)}
            </div>

            <div class="mb-2">
              <strong>Ciutat:</strong>
              ${valueOrDash(inv.ciutat)}
            </div>

            <div class="mb-2">
              <strong>Província:</strong>
              ${valueOrDash(inv.provincia)}
            </div>

            <div>
              <strong>País:</strong>
              ${valueOrDash(inv.pais)}
            </div>

          </div>

        </div>

      </div>

      <!-- EMISSOR -->
      <div class="col-12 col-lg-6">

        <div class="card h-100 shadow-sm">

          <div class="card-header fw-semibold">
            Emissor
          </div>

          <div class="card-body">

            <div class="mb-2">
              <strong>Nom:</strong>
              ${valueOrDash(inv.nomEmissor)}
            </div>

            <div class="mb-2">
              <strong>NIF:</strong>
              ${valueOrDash(inv.nifEmissor)}
            </div>

            <div class="mb-2">
              <strong>Partita IVA:</strong>
              ${valueOrDash(inv.numero_iva)}
            </div>

            <div class="mb-2">
              <strong>Telèfon:</strong>
              ${valueOrDash(inv.telefonEmissor)}
            </div>

            <div class="mb-2">
              <strong>Email:</strong>
              ${valueOrDash(inv.emailEmissor)}
            </div>

            <div class="mb-2">
              <strong>Adreça:</strong>
              ${valueOrDash(inv.adrecaEmissor)}
            </div>

            <div>
              <strong>País:</strong>
              ${valueOrDash(inv.paisEmissor)}
            </div>

          </div>

        </div>

      </div>

    </div>
  `;
}

// ============================================================
// IMPORTS
// ============================================================

function renderInvoiceAmounts(container: HTMLElement, inv: Invoice): void {
  container.innerHTML = `
    <div class="card shadow-sm mb-4">

      <div class="card-header fw-semibold">
        Imports
      </div>

      <div class="card-body">

        <div class="row g-3">

          <div class="col-12 col-sm-6 col-xl-3">
            <div class="small text-muted">
              Subtotal
            </div>
            <div class="fs-5">
              ${formatEUR(inv.base_imposable)}
            </div>
          </div>

          <div class="col-12 col-sm-6 col-xl-3">
            <div class="small text-muted">
              Despeses extres
            </div>
            <div class="fs-5">
              ${formatEUR(inv.despeses_extra)}
            </div>
          </div>

          <div class="col-12 col-sm-6 col-xl-3">
            <div class="small text-muted">
              IVA (${valueOrDash(inv.ivaPercen)}%)
            </div>
            <div class="fs-5">
              ${formatEUR(inv.import_iva)}
            </div>
          </div>

          <div class="col-12 col-sm-6 col-xl-3">
            <div class="small text-muted">
              Total
            </div>
            <div class="fs-4 fw-bold">
              ${formatEUR(inv.total_factura)}
            </div>
          </div>

        </div>

      </div>
    </div>
  `;
}

// ============================================================
// PRODUCTES
// ============================================================

function renderProducts(container: HTMLElement, lines: InvoiceLine[]): void {
  if (!lines.length) {
    container.innerHTML = `
      <div class="alert alert-info">
        Aquesta factura no té línies de producte.
      </div>
    `;

    return;
  }

  const rows = lines
    .map(
      (line) => `
        <tr data-line-id="${escHtml(line.id)}">

          <td class="align-middle">
            ${valueOrDash(line.producte)}
          </td>

          <td class="align-middle">
            ${valueOrDash(line.descripcio)}
          </td>

          <td class="align-middle text-end text-nowrap">
            ${formatEUR(line.preu)}
          </td>

        </tr>
      `
    )
    .join('');

  container.innerHTML = `
    <div class="card shadow-sm mb-4">

      <div class="card-header fw-semibold">
        Detall de productes
      </div>

      <div class="card-body p-0">

        <div class="table-responsive">

          <table class="table table-sm table-striped table-hover align-middle mb-0">

            <thead>
              <tr>
                <th>
                  Producte
                </th>

                <th>
                  Descripció
                </th>

                <th class="text-end">
                  Preu
                </th>
              </tr>
            </thead>

            <tbody>
              ${rows}
            </tbody>

          </table>

        </div>

      </div>

    </div>
  `;
}

// ============================================================
// INFORMACIÓ ADDICIONAL
// ============================================================

function renderInvoiceExtra(container: HTMLElement, inv: Invoice): void {
  const recurrent = inv.recurrent === 1;

  container.innerHTML = `
    <div class="card shadow-sm mb-4">

      <div class="card-header fw-semibold">
        Informació addicional
      </div>

      <div class="card-body">

        <div class="row g-3">

          <div class="col-12 col-md-4">

            <div class="small text-muted">
              Factura recurrent
            </div>

            <div>
              ${recurrent ? '<span class="badge text-bg-success">Sí</span>' : '<span class="badge text-bg-secondary">No</span>'}
            </div>

          </div>

          <div class="col-12 col-md-4">

            <div class="small text-muted">
              Freqüència
            </div>

            <div>
              ${valueOrDash(inv.frequencia)}
            </div>

          </div>

          <div class="col-12 col-md-4">

            <div class="small text-muted">
              Projecte
            </div>

            <div>
              ${valueOrDash(inv.projecte_id)}
            </div>

          </div>

          <div class="col-12">

            <div class="small text-muted">
              Notes
            </div>

            <div class="text-break">
              ${valueOrDash(inv.notes)}
            </div>

          </div>

          ${
            inv.arxiu_url
              ? `
                <div class="col-12">

                  <div class="small text-muted">
                    Arxiu factura
                  </div>

                  <a
                    href="${escHtml(inv.arxiu_url)}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-outline-primary btn-sm"
                  >
                    Obrir factura
                  </a>

                </div>
              `
              : ''
          }

        </div>

      </div>

    </div>
  `;
}

// ============================================================
// INIT
// ============================================================

export async function detallsFacturaClients(idFactura: string): Promise<void> {
  const invoiceId = idFactura;

  if (!invoiceId) {
    return;
  }

  const headerEl = document.getElementById('invoiceHeader');

  const partiesEl = document.getElementById('invoiceParties');

  const amountsEl = document.getElementById('invoiceAmounts');

  const productsEl = document.getElementById('invoiceProducts');

  const extraEl = document.getElementById('invoiceExtra');

  if (!headerEl || !partiesEl || !amountsEl || !productsEl || !extraEl) {
    console.error("No s'han trobat tots els contenidors de la factura.");

    return;
  }

  // ==========================================================
  // LOADING
  // ==========================================================

  headerEl.innerHTML = `
    <div class="placeholder-glow">
      <span class="placeholder col-6"></span>
    </div>
  `;

  partiesEl.innerHTML = `
    <div class="placeholder-glow">
      <span class="placeholder col-12"></span>
    </div>
  `;

  amountsEl.innerHTML = `
    <div class="placeholder-glow">
      <span class="placeholder col-12"></span>
    </div>
  `;

  productsEl.innerHTML = `
    <div class="placeholder-glow">
      <span class="placeholder col-12"></span>
    </div>
  `;

  extraEl.innerHTML = `
    <div class="placeholder-glow">
      <span class="placeholder col-12"></span>
    </div>
  `;

  // ==========================================================
  // GET
  // ==========================================================

  try {
    const data = await getInvoiceData(invoiceId);

    const factura = data.factura;
    const productes = data.productes ?? [];

    renderInvoiceHeader(headerEl, factura);

    renderParties(partiesEl, factura);

    renderInvoiceAmounts(amountsEl, factura);

    renderProducts(productsEl, productes);

    renderInvoiceExtra(extraEl, factura);
  } catch (error) {
    console.error(error);

    headerEl.innerHTML = '';
    partiesEl.innerHTML = '';
    amountsEl.innerHTML = '';

    productsEl.innerHTML = `
      <div class="alert alert-danger">
        S'ha produït un error en carregar la factura.
      </div>
    `;

    extraEl.innerHTML = '';
  }
}
