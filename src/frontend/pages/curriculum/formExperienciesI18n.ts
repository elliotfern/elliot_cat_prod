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
  experiencia_id: number;
  locale: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formExperienciesI18n(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formCVExperienciaI18n');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnExperienciai18n') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.EXPERIENCIA_I18N_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació experiència professional i18n del currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVExperienciaI18n', API_URLS.PUT.EXPERIENCIA_I18N);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova experiència professional i18n del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVExperienciaI18n', API_URLS.POST.EXPERIENCIA_I18N, true);
    });
  }

  await auxiliarSelect(data.experiencia_id ?? 0, 'experiencies', 'experiencia_id', 'empresa');
  await auxiliarSelect(data.locale ?? 0, 'llengues', 'locale', 'idioma_ca');
}
