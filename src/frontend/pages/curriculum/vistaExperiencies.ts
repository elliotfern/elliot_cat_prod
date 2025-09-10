// src/pages/curriculum/vistaExperiencia.ts
import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';

interface ExperienciaItem {
  id: number;
  empresa: string;
  empresa_url?: string | null;
  empresa_localitzacio?: number | null;
  data_inici: string;
  data_fi?: string | null;
  is_current: 0 | 1 | boolean;
  logo_empresa?: number | null;
  posicio: number;
  visible: 0 | 1 | boolean;
}

interface ApiResponse<T> {
  status: 'success' | 'error';
  message: string;
  data: T;
}

const esc = (s: unknown) =>
  String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const spinner = () => `<div class="d-flex align-items-center"><div class="spinner-border me-2" role="status" aria-hidden="true"></div> Carregant…</div>`;

const errorBox = (msg: string) => `<div class="alert alert-danger" role="alert">${esc(msg)}</div>`;

const emptyBox = (msg = 'No hi ha experiències registrades.') => `<div class="alert alert-secondary" role="alert">${esc(msg)}</div>`;

// formateo fechas simple (YYYY-MM-DD → MM/YYYY)
function fmtDate(dateStr?: string | null): string {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return `${String(d.getUTCMonth() + 1).padStart(2, '0')}/${d.getUTCFullYear()}`;
}

function renderTable(items: ExperienciaItem[]): string {
  if (!items.length) return emptyBox();

  // Ordenar por posicio
  items.sort((a, b) => a.posicio - b.posicio || a.id - b.id);

  const rows = items
    .map((it) => {
      const vis = it.visible === 1 || it.visible === true;
      const current = it.is_current === 1 || it.is_current === true;

      const periode = current ? `${fmtDate(it.data_inici)} - actual` : `${fmtDate(it.data_inici)} - ${fmtDate(it.data_fi)}`;

      const detailHref = `https://elliot.cat/gestio/curriculum/perfil-experiencia-professional/${it.id}`;
      const editHref = `https://elliot.cat/gestio/curriculum/modifica-experiencia/${it.id}`;

      return `
        <tr>
          <td class="fw-semibold">
            <a href="${esc(detailHref)}">${esc(it.empresa)}</a>
          </td>
          <td>${esc(periode)}</td>
          <td>
            <span class="badge ${vis ? 'bg-success' : 'bg-secondary'}">
              ${vis ? 'Visible' : 'Ocult'}
            </span>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="${esc(editHref)}">Modifica</a>
          </td>
        </tr>
      `;
    })
    .join('');

  return `
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Període</th>
            <th style="width:8rem">Estat</th>
            <th style="width:8rem" class="text-end">Accions</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}

export async function vistaExperiencia(): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;
  root.innerHTML = spinner();

  try {
    const url = API_URLS.GET.EXPERIENCIES;
    const res = await fetchDataGet<ApiResponse<ExperienciaItem[]>>(url, true);

    if (res) {
      if (res.status !== 'success') {
        root.innerHTML = errorBox(res.message || 'Error desconegut');
        return;
      }

      const items = Array.isArray(res.data) ? res.data : [];
      root.innerHTML = renderTable(items);
    }
  } catch (e: any) {
    root.innerHTML = errorBox(e?.message ?? 'Error carregant les dades');
  }
}
