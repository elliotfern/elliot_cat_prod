import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { CursArticle } from '../../types/CursArticle';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';

export async function formCursArticle(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formCursArticle') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnCursArticle') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  // Datos por defecto (crear)
  let data: Partial<CursArticle> = {};

  // Si vienes con ?cursId=... (crear desde una fitxa)
  const qs = new URLSearchParams(window.location.search);
  const cursIdFromQuery = String(qs.get('cursId') ?? 0);
  if (cursIdFromQuery) {
    data.curs_id = cursIdFromQuery;
  }

  if (id && isUpdate) {
    try {
      data = await api.get<CursArticle>(API_URLS.GET.HISTORIA_CURS_ARTICLE_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

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
  await auxiliarSelect(data.curs_id ?? 0, 'historiaCursos', 'curs_id', 'nomCurs');
  await auxiliarSelect(data.article_ca_id ?? 0, 'blogArticlesCa', 'article_ca_id', 'post_title');
  await auxiliarSelect(data.article_es_id ?? 0, 'blogArticlesEs', 'article_es_id', 'post_title');
  await auxiliarSelect(data.article_en_id ?? 0, 'blogArticlesEn', 'article_en_id', 'post_title');
  await auxiliarSelect(data.article_fr_id ?? 0, 'blogArticlesFr', 'article_fr_id', 'post_title');
  await auxiliarSelect(data.article_it_id ?? 0, 'blogArticlesIt', 'article_it_id', 'post_title');
}
