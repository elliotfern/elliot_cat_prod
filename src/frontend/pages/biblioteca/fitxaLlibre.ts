import { getLangPrefix, isInGestio } from '../../utils/locales/getLangPrefix';
import { DOMAIN_WEB } from '../../utils/urls';

type ApiResponse<T> = {
  status: 'success' | 'error';
  message: string;
  errors: any[] | Record<string, any>;
  data: T;
};

type AutorData = {
  id: string;
  nom: string | null;
  cognoms: string | null;
  slug: string | null;
};

type BookData = {
  id: string;
  titol: string;
  slug: string;
  any: number;

  dateCreated: string | null;
  dateModified: string | null;

  lang: number | string;
  img: number | string;
  nameImg: string;

  nomTipus: string | null;
  editorial: string | null;
  idioma_ca: string | null;

  // ahora es UUID v7 (según dices)
  estat: string | null;
  nomEstat: string;

  sub_tema_ca: string;
  tema_ca: string;

  // NUEVO: array de autores
  autors?: AutorData[];

  // Compat legacy (por si algún endpoint viejo todavía devuelve esto)
  id_autor?: string;
  autorSlug?: string;
  nom?: string | null;
  cognoms?: string | null;
  llibreSlug?: string;
};

function setText(id: string, value: any) {
  const el = document.getElementById(id);
  if (el) el.textContent = value == null ? '' : String(value);
}

function setHtml(id: string, value: string) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = value;
}

function setImg(id: string, src: string) {
  const el = document.getElementById(id) as HTMLImageElement | null;
  if (el) el.src = src;
}

function setHref(id: string, href: string) {
  const el = document.getElementById(id) as HTMLAnchorElement | null;
  if (el) el.href = href;
}

function formatDateES(dateStr: string | null): string {
  if (!dateStr) return '';
  const iso = dateStr.includes(' ') ? dateStr.replace(' ', 'T') : dateStr; // "YYYY-MM-DD HH:MM:SS" -> ISO
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleDateString('es-ES').replace(/\//g, '-');
}

function escapeHtml(s: string): string {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// Renderiza 1 o N autores en el DOM
// - Si hay 1 autor: usa el <a id="linkAutor"> como link normal
// - Si hay varios: convierte #linkAutor en contenedor HTML con links
function renderAutors(data: BookData) {
  const el = document.getElementById('linkAutor');
  if (!el) return;

  const basePrefix = isInGestio() ? 'gestio' : getLangPrefix();
  const base = `${DOMAIN_WEB}/${basePrefix}/biblioteca/fitxa-autor/`;

  // 1) Preferimos array autors[]
  const autors = Array.isArray(data.autors) ? data.autors.filter((a) => a && a.slug) : [];

  // 2) Fallback legacy si no viene autors[]
  if (autors.length === 0 && data.autorSlug) {
    const fullName = [data.nom, data.cognoms].filter(Boolean).join(' ').trim();
    const label = escapeHtml(fullName || data.autorSlug);

    el.innerHTML = `Autor: <a href="${base}${encodeURIComponent(data.autorSlug)}">${label}</a>`;
    return;
  }

  // 3) Sin autores
  if (autors.length === 0) {
    el.textContent = ''; // o "Autor: —"
    return;
  }

  // 4) Pintar 1 o N con etiqueta
  const etiqueta = autors.length === 1 ? 'Autor' : 'Autors';

  const links = autors
    .map((a) => {
      const fullName = [a.nom, a.cognoms].filter(Boolean).join(' ').trim();
      const label = escapeHtml(fullName || a.slug || '');
      const href = `${base}${encodeURIComponent(a.slug || '')}`;
      return `<a href="${href}">${label}</a>`;
    })
    .join(' / ');

  el.innerHTML = `<strong>${etiqueta}:</strong> ${links}`;
}

// Función para realizar la solicitud a la API
export function fetchApiDataLlibre(url: string) {
  fetch(url, {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' },
  })
    .then((response) => {
      if (!response.ok) throw new Error('Network response was not ok');
      return response.json();
    })
    .then((resp: ApiResponse<BookData> | BookData | BookData[]) => {
      console.log('Datos recibidos:', resp);

      // Compat: array / wrapper / objeto
      let data: BookData;
      if (Array.isArray(resp)) {
        data = resp[0];
      } else if ((resp as any).data) {
        data = (resp as ApiResponse<BookData>).data;
      } else {
        data = resp as BookData;
      }

      const fechaCre = formatDateES(data.dateCreated ?? null);
      const fechaMod = formatDateES(data.dateModified ?? null);

      // DOM básicos
      setText('titolBook', data.titol);
      setImg('nameImg', `https://media.elliot.cat/img/biblioteca-llibre/${data.nameImg}.jpg`);

      // Autores (1 o varios)
      renderAutors(data);

      // Campos legacy (algunos ya no existen)
      setText('titolEng', ''); // no viene
      setText('genere_cat', data.tema_ca ?? '');
      setText('sub_genere_cat', data.sub_tema_ca ?? '');

      setText('any', data.any);
      setText('editorial', data.editorial);
      setText('idioma_ca', data.idioma_ca);
      setText('nomTipus', data.nomTipus);
      setText('estat', data.nomEstat);

      // Fechas
      setText('dateCreated', fechaCre);

      if (!fechaMod || fechaMod === '0000-00-00' || fechaMod === fechaCre) {
        setText('dateModified', '');
      } else {
        setHtml('dateModified', `| <strong> Darrera modificació: </strong> ${fechaMod}`);
      }
    })
    .catch((error) => {
      console.error('Error en la solicitud:', error);
    });
}
