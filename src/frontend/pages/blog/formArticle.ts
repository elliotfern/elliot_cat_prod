import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { setTrixHTML } from '../../utils/setTrix';

interface BlogArticleFitxa {
  [key: string]: unknown;

  id: number;
  post_type: string;
  post_title: string;
  post_content: string;
  post_excerpt?: string | null;
  lang: number; // int(1)
  post_status: string;
  slug: string;
  categoria: string; // binary(16) -> normalment ho representarem com hex/uuid string al frontend
  post_date: string; // datetime
  post_modified: string; // datetime
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

function fillFormFromData(form: HTMLFormElement, data: Record<string, unknown>) {
  // 1) Inputs/textarea/select por NAME
  for (const [key, val] of Object.entries(data)) {
    const el = form.querySelector<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>(`[name="${key}"]`);
    if (!el) continue;

    const v = val ?? '';
    if (el instanceof HTMLInputElement && (el.type === 'checkbox' || el.type === 'radio')) {
      el.checked = Boolean(v);
    } else {
      el.value = String(v);
    }
  }

  // 2) Fallback: si algo está solo por ID (por si algún campo no tiene name)
  for (const [key, val] of Object.entries(data)) {
    const el = document.getElementById(key) as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement | null;
    if (!el) continue;
    if ('value' in el && (el as any).value === '') el.value = String(val ?? '');
  }
}

/**
 * Formulari Blog Article (Create / Update)
 * - View PHP: /gestio/blog/modifica-article/{id} (update)
 * - View PHP: /gestio/blog/nou-article (create) (o la que sigui)
 */
export async function formBlogArticle(isUpdate: boolean, id?: number) {
  const form = document.getElementById('modificaBlog') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolBlog') as HTMLHeadingElement | null;
  const btnSubmit = document.getElementById('btnSave') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  // Defaults mínims (tu pots canviar-los)
  let data: Partial<BlogArticleFitxa> = {
    post_type: 'post',
    post_status: 'publish',
    lang: 1,
    post_excerpt: '',
    post_content: '',
  };

  // 1) Omplir selects dinàmics (abans de renderFormInputs)
  // ⚠️ Ajusta aquests "datasets" i camps quan fem endpoints.
  // auxiliarSelect(valorSeleccionat, dataset, selectId, campLabel)
  async function fillSelects(current: Partial<BlogArticleFitxa>) {
    await Promise.all([
      // Categoria (binary16): normalment aquí tindràs una taula de categories/temes amb uuid + nom
      auxiliarSelect(current.categoria ?? '', 'temes', 'categoria', 'tema_ca'),

      // Idiomes (int): taula d'idiomes amb id + nom
      auxiliarSelect(current.lang ?? 1, 'llengues', 'lang', 'idioma_ca'),

      // Estat (varchar): si ho tens en taula, sinó també es pot omplir amb constants al TS (però tu has dit dinàmic)
      auxiliarSelect(current.post_status ?? 'publicat', 'estatsPublicacio', 'post_status', 'post_status'),

      // Tipus (varchar): idem
      auxiliarSelect(current.post_type ?? 'article', 'tipusPublicacio', 'post_type', 'post_type'),
    ]);
  }

  if (isUpdate && id) {
    // 2) GET fitxa
    const response = await fetchDataGet<ApiResponse<BlogArticleFitxa>>(API_URLS.GET.BLOG_ARTICLE_ID(id), true);
    if (!response || !response.data) return;

    const row = Array.isArray(response.data) ? response.data[0] : response.data;
    if (!row) return;

    data = row;

    // 3) Títol UI
    divTitol.textContent = 'Modificar article';
    btnSubmit.textContent = 'Desar canvis';

    await fillSelects(data);

    // 1) tu helper (si quieres mantenerlo)
    renderFormInputs(data);

    // 2) ✅ relleno seguro (esto te arregla los text inputs sí o sí)
    fillFormFromData(form, data as Record<string, unknown>);

    // 5) Dates (datetime-local)
    const postDateEl = document.getElementById('post_date') as HTMLInputElement | null;
    const postModEl = document.getElementById('post_modified') as HTMLInputElement | null;

    // 6) Trix
    setTrixHTML('post_content', String(data.post_content ?? ''));

    // 8) Submit PUT
    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'modificaBlog', API_URLS.PUT.BLOG_ARTICLE);
    });
  } else {
    // CREATE
    divTitol.textContent = 'Crear nou article';
    btnSubmit.textContent = 'Crear article';

    await fillSelects(data);

    // post_modified readonly (buit)
    const postModEl = document.getElementById('post_modified') as HTMLInputElement | null;
    if (postModEl) postModEl.value = '';

    // Trix buit
    setTrixHTML('post_content', '');

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'modificaBlog', API_URLS.POST.BLOG_ARTICLE, true);
    });
  }
}
