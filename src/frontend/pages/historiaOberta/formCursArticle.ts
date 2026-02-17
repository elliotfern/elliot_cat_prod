import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

type SlotCursArticle = {
  id: number;
  ca: number | null;
  es: number | null;
  fr: number | null;
  en: number | null;
  it: number | null;
  curs: number;
  ordre: number;
};

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

/**
 * Form per crear/modificar db_historia_oberta_articles (slot)
 *
 * - Update: /gestio/historia/modifica-curs-article/{idSlot}
 * - Create: /gestio/historia/nou-curs-article?cursId=3 (si quieres)
 */
export async function formCursArticle(isUpdate: boolean, idSlot?: number) {
  const form = document.getElementById('formCursArticle') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnCursArticle') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  // Datos por defecto (crear)
  let data: Partial<SlotCursArticle> = {
    ca: null,
    es: null,
    en: null,
    fr: null,
    it: null,
    curs: 0,
    ordre: 1,
  };

  // Si vienes con ?cursId=... (crear desde una fitxa)
  const qs = new URLSearchParams(window.location.search);
  const cursIdFromQuery = Number(qs.get('cursId') ?? 0);
  if (!isNaN(cursIdFromQuery) && cursIdFromQuery > 0) {
    data.curs = cursIdFromQuery;
  }

  if (idSlot && isUpdate) {
    // ---------- UPDATE ----------
    const response = await fetchDataGet<ApiResponse<SlotCursArticle>>(API_URLS.GET.HISTORIA_CURS_ARTICLE_ID(idSlot), true);
    if (!response || !response.data) return;

    data = response.data;

    divTitol.innerHTML = `<h2>Modifica slot del curs</h2>`;
    btnSubmit.textContent = 'Guardar canvis';

    // Pintar valores en inputs/selects (renderInputsForm usa name/id = keys)
    renderFormInputs(data);

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCursArticle', API_URLS.PUT.HISTORIA_CURS_ARTICLE);
    });
  } else {
    // ---------- CREATE ----------
    divTitol.innerHTML = `<h2>Nou slot del curs</h2>`;
    btnSubmit.textContent = 'Crear';

    // Pintar defaults (incluye cursId si venía por querystring)
    renderFormInputs(data);

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCursArticle', API_URLS.POST.HISTORIA_CURS_ARTICLE, true);
    });
  }

  // ---------------------------------------------
  // Selects
  // ---------------------------------------------
  await auxiliarSelect(data.curs ?? 0, 'historiaCursos', 'curs', 'nomCurs');
  await auxiliarSelect(data.ca ?? 0, 'blogArticlesCa', 'ca', 'post_title');
  await auxiliarSelect(data.es ?? 0, 'blogArticlesEs', 'es', 'post_title');
  await auxiliarSelect(data.en ?? 0, 'blogArticlesEn', 'en', 'post_title');
  await auxiliarSelect(data.fr ?? 0, 'blogArticlesFr', 'fr', 'post_title');
  await auxiliarSelect(data.it ?? 0, 'blogArticlesIt', 'it', 'post_title');
}
