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
  imatge_id: number;
  locale: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formHabilitats(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formCVHabilitats');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnCVPerfili18n') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const id = 1;
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.HABILITAT_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació habilitats currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVHabilitats', API_URLS.PUT.HABILITAT);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova habilitat del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVHabilitats', API_URLS.POST.HABILITAT, true);
    });
  }

  await auxiliarSelect(data.imatge_id ?? 0, 'imatgesIcones', 'imatge_id', 'nom');
}
