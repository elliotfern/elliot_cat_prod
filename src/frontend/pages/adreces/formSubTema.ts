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
  institucio_localitzacio: number;
  logo_id: number;
  tema_id: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formSubTema(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formSubTema');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnSubTema') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.SUBTEMA_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació Sub-tema</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formSubTema', API_URLS.PUT.SUBTEMA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Sub-tema</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formSubTema', API_URLS.POST.SUBTEMA, true);
    });
  }

  await auxiliarSelect(data.tema_id ?? 0, 'subtemes', 'tema_id', 'sub_tema_ca');
}
