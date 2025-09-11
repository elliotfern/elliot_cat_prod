import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { setTrixHTML } from '../../utils/setTrix';

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
  educacio_id: number;
  locale: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formEducacioi18(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formEducacioI18n');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnEducacioi18n') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.EDUCACIO_I18N_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació educació i18n del currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEducacioI18n', API_URLS.PUT.EDUCACIO_I18N);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou títol i18n educatiu del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEducacioI18n', API_URLS.POST.EDUCACIO_I18N, true);
    });
  }

  await auxiliarSelect(data.locale ?? 0, 'llengues', 'locale', 'idioma_ca');
  await auxiliarSelect(data.educacio_id ?? 0, 'educacions', 'educacio_id', 'institucio_periode');
}
