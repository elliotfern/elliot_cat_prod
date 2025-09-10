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
  logo_empresa: number;
  empresa_localitzacio: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formExperiencies(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formCVExperiencia');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnExperiencia') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.EXPERIENCIA_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació experiència professional del currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVExperiencia', API_URLS.PUT.EXPERIENCIA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova experiència professional del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVExperiencia', API_URLS.POST.EXPERIENCIA, true);
    });
  }

  await auxiliarSelect(data.logo_empresa ?? 0, 'imatgesEmpreses', 'logo_empresa', 'nom');
  await auxiliarSelect(data.empresa_localitzacio ?? 0, 'ciutats', 'empresa_localitzacio', 'city');
}
