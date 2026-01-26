import { fetchDataGet } from '../../services/api/fetchData';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_IMG } from '../../utils/urls';

interface Educacio {
  id: number;
  institucio: string;
  institucio_url?: string | null;
  institucio_localitzacio?: string | null;
  data_inici?: string | null;
  data_fi?: string | null;
  logo_id?: number | null;
  posicio: number;
  visible: number | boolean;
  created_at: string;
  updated_at: string;

  // extra de la API
  nameImg?: string | null;
  ciutat?: string | null;
  pais_ca?: string | null;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T[];
}

const esc = (s: unknown) =>
  String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

const spinner = () => `<div class="d-flex align-items-center"><div class="spinner-border me-2" role="status"></div> Carregant…</div>`;

/** Formatea fecha YYYY-MM-DD → MM/YYYY */
function fmtDate(dateStr?: string | null): string {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return '';
  return `${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
}

function renderTable(rows: Educacio[]): string {
  if (!rows.length) {
    return `<div class="alert alert-secondary">No hi ha registres d'educació.</div>`;
  }

  const body = rows
    .map((r) => {
      const logoUrl = r.nameImg ? `${DOMAIN_IMG}/img/logos-empreses/${r.nameImg}.png` : null;
      const localitzacio = [r.ciutat, r.pais_ca].filter(Boolean).join(', ');
      const periode = `${fmtDate(r.data_inici)} - ${fmtDate(r.data_fi) || 'actualitat'}`;

      const editHref = `https://elliot.cat/gestio/curriculum/modifica-educacio/${r.id}`;
      const detailHref = `https://elliot.cat/gestio/curriculum/perfil-educacio-i18n/${r.id}`;

      return `
        <tr>
          <td>${logoUrl ? `<img src="${esc(logoUrl)}" alt="" style="height:30px">` : ''}</td>
          <td>${esc(r.institucio)}</td>
          <td>${r.institucio_url ? `<a href="${esc(r.institucio_url)}" target="_blank">${esc(r.institucio_url)}</a>` : ''}</td>
          <td>${esc(localitzacio)}</td>
          <td>${periode}</td>
          <td>
            <a class="btn btn-sm btn-outline-primary me-2" href="${esc(editHref)}">Modifica</a>
            <a class="btn btn-sm btn-outline-secondary" href="${esc(detailHref)}">Detalls</a>
          </td>
        </tr>
      `;
    })
    .join('');

  return `
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Logo</th>
          <th>Institució</th>
          <th>Web</th>
          <th>Localització</th>
          <th>Període</th>
          <th>Accions</th>
        </tr>
      </thead>
      <tbody>
        ${body}
      </tbody>
    </table>
  `;
}

export async function vistaEducacio(): Promise<void> {
  const root = document.getElementById('apiResults');
  if (!root) return;
  root.innerHTML = spinner();

  try {
    const url = API_URLS.GET.EDUCACIO_CV;
    const res = await fetchDataGet<ApiResponse<Educacio>>(url, true);

    if (res) {
      if (res.status !== 'success') {
        root.innerHTML = `<div class="alert alert-danger">${esc(res.message)}</div>`;
        return;
      }

      root.innerHTML = renderTable(res.data);
    }
  } catch (e: any) {
    root.innerHTML = `<div class="alert alert-danger">${esc(e?.message ?? 'Error carregant dades')}</div>`;
  }
}
