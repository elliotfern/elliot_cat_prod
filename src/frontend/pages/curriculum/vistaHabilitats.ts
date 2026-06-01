// src/pages/curriculum/vistaHabilitats.ts
import { api } from '../../core/api/client';
import { HabilitatItem } from '../../types/Curriculum';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_IMG } from '../../utils/urls';

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

      const editHref = `/gestio/curriculum/modifica-habilitat/${it.id}`;

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

  let data: HabilitatItem[];

  try {
    data = await api.get<HabilitatItem[]>(API_URLS.GET.HABILITATS);
    root.innerHTML = renderTable(data);
  } catch (error) {
    console.error(error);
    root.innerHTML = errorBox('Error desconegut');
    return;
  }
}
