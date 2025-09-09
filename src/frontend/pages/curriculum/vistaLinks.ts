// src/pages/curriculum/vistaLinks.ts
import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_IMG } from '../../utils/urls';

interface LinkItem {
  id: number;
  perfil_id: number;
  label: string | null;
  url: string;
  posicio: number;
  visible: 0 | 1 | boolean;
  nameImg: string;
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

// Helper de iconos (corrige la extensión a .png, acepta URLs absolutas o nombres ya con extensión)
function resolveImg(nameImg?: string | null): string | null {
  if (!nameImg) return null;
  if (/^https?:\/\//i.test(nameImg)) return nameImg; // ya es URL
  const withExt = /\.\w{3,4}$/.test(nameImg) ? nameImg : `${nameImg}.png`;
  return `${DOMAIN_IMG}/img/web-icones/${withExt}`;
}

function renderTable(items: LinkItem[]): string {
  if (!items.length) return emptyBox();

  items.sort((a, b) => a.posicio - b.posicio || a.id - b.id);

  const rows = items
    .map((it) => {
      const vis = it.visible === 1 || it.visible === true;
      const label =
        it.label && it.label.trim() !== ''
          ? it.label
          : (() => {
              try {
                return new URL(it.url).hostname;
              } catch {
                return it.url;
              }
            })();
      const urlDisplay = it.url.length > 70 ? it.url.slice(0, 67) + '…' : it.url;

      const iconUrl = resolveImg(it.nameImg);
      const iconHTML = iconUrl ? `<img src="${esc(iconUrl)}" alt="" width="18" height="18" style="object-fit:contain;vertical-align:-3px">` : `<span class="text-muted">—</span>`;

      const editHref = `https://elliot.cat/gestio/curriculum/modifica-link/${it.id}`;

      return `
      <tr>
      <td class="text-center" style="width:3rem">${iconHTML}</td>
        <td class="text-nowrap">${esc(it.posicio)}</td>
        <td class="fw-semibold">${esc(label)}</td>
        <td>
          <a href="${esc(it.url)}" target="_blank" rel="noopener noreferrer">${esc(urlDisplay)}</a>
        </td>
        <td>
          <span class="badge ${vis ? 'bg-success' : 'bg-secondary'}">${vis ? 'Visible' : 'Ocult'}</span>
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
            <th style="width:6rem">Pos.</th>
            <th>Etiqueta</th>
            <th>URL</th>
            <th style="width:8rem">Estat</th>
            <th style="width:8rem" class="text-end">Accions</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}

export async function vistaLinks(): Promise<void> {
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
