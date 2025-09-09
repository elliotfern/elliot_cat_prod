// src/pages/curriculum/vistaHabilitats.ts
import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_IMG } from '../../utils/urls';

interface HabilitatItem {
  id: number;
  nom: string;
  imatge_id?: number | null;
  nameImg?: string | null; // campo devuelto por la API con el nombre del archivo del icono
  posicio: number;
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

const emptyBox = (msg = 'No hi ha habilitats registrades.') => `<div class="alert alert-secondary" role="alert">${esc(msg)}</div>`;

// Helper para resolver URL de iconos
function resolveImg(nameImg?: string | null): string | null {
  if (!nameImg) return null;
  if (/^https?:\/\//i.test(nameImg)) return nameImg; // ya es URL
  const withExt = /\.\w{3,4}$/.test(nameImg) ? nameImg : `${nameImg}.png`;
  return `${DOMAIN_IMG}/img/web-icones/${withExt}`;
}

function renderTable(items: HabilitatItem[]): string {
  if (!items.length) return emptyBox();

  // Ordena por posicio ASC
  items.sort((a, b) => a.posicio - b.posicio || a.id - b.id);

  const rows = items
    .map((it) => {
      const iconUrl = resolveImg(it.nameImg);
      const iconHTML = iconUrl ? `<img src="${esc(iconUrl)}" alt="" width="22" height="22" style="object-fit:contain;vertical-align:-3px">` : `<span class="text-muted">—</span>`;

      const editHref = `https://elliot.cat/gestio/curriculum/modifica-habilitat/${it.id}`;

      return `
        <tr>
          <td class="text-center" style="width:3rem">${iconHTML}</td>
          <td class="fw-semibold">${esc(it.nom)}</td>
          <td class="text-nowrap">${esc(it.posicio)}</td>
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
            <th style="width:3rem" class="text-center">Icona</th>
            <th>Habilitat</th>
            <th style="width:6rem">Pos.</th>
            <th style="width:8rem" class="text-end">Accions</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}

export async function vistaHabilitats(): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;
  root.innerHTML = spinner();

  try {
    const url = API_URLS.GET.HABILITATS;
    const res = await fetchDataGet<ApiResponse<HabilitatItem[]>>(url, true);

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

// auto-init
document.addEventListener('DOMContentLoaded', () => {
  vistaHabilitats();
});
