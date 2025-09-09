import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Fitxa {
  [key: string]: unknown;
  status: string;
  message: string;
  id: number;
  espai_cat: string;
  municipi: number;
  comarca: number;
  provincia: number;
  comunitat: number;
  estat: number;
  perfil_id: number;
  locale: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formPerfilI18n(isUpdate: boolean, idLocale?: number) {
  const form = document.getElementById('formCVPerfilI18n');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnCVPerfili18n') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (idLocale && isUpdate) {
    const id = 1;
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.PERFIL_CV_I18N_ID(id, idLocale), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació perfil i18n currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVPerfilI18n', API_URLS.PUT.PERFIL_CV_I18N);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou perfil i18n del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVPerfilI18n', API_URLS.POST.PERFIL_CV_I18N, true);
    });
  }

  await auxiliarSelect(data.perfil_id ?? 0, 'perfilsCV', 'perfil_id', 'nom_complet');
  await auxiliarSelect(data.locale ?? 0, 'llengues', 'locale', 'idioma_ca');
}
