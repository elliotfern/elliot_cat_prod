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
  locale: number;
  fites: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formEducacio(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formEducacio');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnEducacio') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.EDUCACIO_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació educació del currículum</h2>`;

    renderFormInputs(data);

    // Carga robusta en Trix (después de que Trix se haya inicializado)
    await setTrixHTML('fites', data.fites);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEducacio', API_URLS.PUT.EDUCACIO_CV);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou títol educatiu del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEducacio', API_URLS.POST.EDUCACIO_CV, true);
    });
  }

  await auxiliarSelect(data.experiencia_id ?? 0, 'experiencies', 'experiencia_id', 'empresa');
  await auxiliarSelect(data.locale ?? 0, 'llengues', 'locale', 'idioma_ca');
}
