import { api } from '../../core/api/client';
import { PerfilCV } from '../../types/Curriculum';
import { API_URLS } from '../../utils/apiUrls';
import { DOMAIN_IMG, DOMAIN_WEB } from '../../utils/urls';

// Mapa opcional per mostrar disponibilitat (ajusta si tens catàleg propi)
const DISPON: Record<number, string> = {
  1: 'Immediata',
  2: 'Amb preavís',
  3: 'Freelance',
  4: 'Mitja jornada',
  5: 'Jornada completa',
};

const qsId = (): number => {
  const v = new URLSearchParams(window.location.search).get('id');
  const n = v ? Number(v) : NaN;
  return Number.isFinite(n) && n > 0 ? n : 1;
};

const fmtDT = (iso?: string) => {
  if (!iso) return '—';
  const d = new Date(iso);
  return Number.isNaN(d.getTime()) ? iso : d.toLocaleString('ca-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
};

const esc = (s: unknown) =>
  String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

function resolveImg(nameImg?: string | null): string | null {
  if (!nameImg) return null;
  if (/^https?:\/\//i.test(nameImg)) return nameImg; // ja és URL
  // si només és el nom de fitxer, adapta la base a la teva ruta pública
  return `${DOMAIN_IMG}/img/usuaris-avatar/${nameImg}.jpg`;
}

function renderCard(d: PerfilCV): string {
  const imgUrl = resolveImg(d.nameImg);
  const dispoTxt = d.disponibilitat && DISPON[d.disponibilitat] ? DISPON[d.disponibilitat] : '—';
  const visTxt = d.visibilitat === 1 || d.visibilitat === true ? 'Sí' : 'No';
  const urlEdit = editUrl(d.id);

  return `
    <div class="container-fluid form">
        <div class="row g-3 align-items-center">
          <div class="col-auto">
            ${imgUrl ? `<img src="${esc(imgUrl)}" alt="Foto perfil" class="rounded" style="width:130px;height:130px;object-fit:cover;padding:15px">` : `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:130px;height:130px;">—</div>`}
          </div>
          <div class="col">
            <h2 class="h4 mb-1">${esc(d.nom_complet)}</h2>
            <div class="text-muted small">
            <p><span><strong>Adreça:</strong> ${esc(d.adreca ?? '—')} (${esc(d.ciutat_ca ?? '—')} - ${esc(d.pais_ca ?? '—')} )</span></p>
            </div>
            <div class="mt-2">
              <p><a href="mailto:${esc(d.email)}" class="me-3">${esc(d.email)}</a></p>
              <p>${d.tel ? `<span class="me-3">📞 ${esc(d.tel)}</span>` : ''}</p>
              <p>${d.web ? `<a href="${/^https?:\/\//i.test(d.web) ? esc(d.web) : `https://${esc(d.web)}`}" target="_blank" rel="noopener">🌐 ${esc(d.web)}</a>` : ''}</p>
            </div>
          </div>
        </div>
        <hr>
        <div class="text-muted small">
          <span><strong>Darrera actualització:</strong> ${esc(fmtDT(d.updated_at))}</span>
        </div>
        <div class="mt-3 text-end">
        <a class="btn btn-sm btn-outline-primary" href="${esc(urlEdit)}"
           aria-label="Editar">
          ✏️ Edita
        </a>
      </div>
    </div>
  `;
}

function show(container: HTMLElement, html: string) {
  container.innerHTML = html;
}

function spinner(): string {
  return `<div class="d-flex align-items-center"><div class="spinner-border me-2" role="status" aria-hidden="true"></div> Carregant…</div>`;
}

function errorBox(msg: string): string {
  return `<div class="alert alert-danger" role="alert">${esc(msg)}</div>`;
}

// URL del botón de edición
const editUrl = (id: number) => `${DOMAIN_WEB}/gestio/curriculum/modifica-perfil/${id}`;

export async function vistaPerfilCV(): Promise<void> {
  const container = document.getElementById('apiResults');
  if (!container) {
    console.error('#apiResults no trobat');
    return;
  }

  show(container, spinner());

  const id = qsId();
  let data: PerfilCV;

  try {
    data = await api.get<PerfilCV>(API_URLS.GET.PERFIL_CV_ID, {
      id,
    });
    show(container, renderCard(data));
  } catch (error) {
    console.error(error);
    show(container, errorBox('Error desconegut'));
    return;
  }
}
