// src/pages/curriculum/vistaLinks.ts
import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';

interface LinkItem {
  id: number;
  perfil_id: number;
  label: string | null;
  url: string;
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
const emptyBox = (msg = 'No hi ha enllaços per a aquest perfil.') => `<div class="alert alert-secondary" role="alert">${esc(msg)}</div>`;

const qsPerfilId = (): number => {
  const v = new URLSearchParams(window.location.search).get('perfil_id');
  const n = v ? Number(v) : NaN;
  return Number.isFinite(n) && n > 0 ? n : 1;
};

function renderTable(items: LinkItem[]): string {
  if (!items.length) return emptyBox();

  // ordenar por posicio ASC, luego id
  items.sort((a, b) => a.posicio - b.posicio || a.id - b.id);

  const rows = items
    .map((it) => {
      const vis = it.visible === 1 || it.visible === true;
      const label = it.label && it.label.trim() !== '' ? it.label : new URL(it.url).hostname;
      const urlDisplay = it.url.length > 70 ? it.url.slice(0, 67) + '…' : it.url;

      return `
      <tr>
        <td class="text-nowrap">${esc(it.posicio)}</td>
        <td class="fw-semibold">${esc(label)}</td>
        <td>
          <a href="${esc(it.url)}" target="_blank" rel="noopener noreferrer">${esc(urlDisplay)}</a>
        </td>
        <td>
          <span class="badge ${vis ? 'bg-success' : 'bg-secondary'}">${vis ? 'Visible' : 'Ocult'}</span>
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
            <th style="width:6rem">Pos.</th>
            <th>Etiqueta</th>
            <th>URL</th>
            <th style="width:8rem">Estat</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}

export async function vistaLinks(perfilId = qsPerfilId()): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;
  root.innerHTML = spinner();

  try {
    const url = API_URLS.GET.LINKS_CV;
    const res = await fetchDataGet<ApiResponse<LinkItem[]>>(url, true);

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
