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
  localitzacio_ciutat: number;
  img_perfil: number;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formPerfil(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formCVPerfil');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnCVPerfil') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.PERFIL_CV_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació perfil currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVPerfil', API_URLS.PUT.PERFIL_CV);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou perfil del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVPerfil', API_URLS.POST.PERFIL_CV, true);
    });
  }

  await auxiliarSelect(data.img_perfil ?? 0, 'imatgesUsuaris', 'img_perfil', 'nom');
  await auxiliarSelect(data.localitzacio_ciutat ?? 0, 'ciutats', 'localitzacio_ciutat', 'ciutat');
}
