import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Fitxa {
  [key: string]: unknown;
  grup_ids?: string[];
  status: string;
  message: string;
  id: string;
  ciutat_id: string;
  descripcio: string;
  img_id: string;
  tipus_id: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formEspai(isUpdate: boolean, slug?: string) {
  const form = document.getElementById('formEspai');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnEspai') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
    id: '',
    descripcio: '',
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (slug && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(`https://elliot.cat/api/viatges/get/fitxaEspai?espai=${slug}`, true);

    if (!response || !response.data) return;
    data = response.data;
    console.log(data);

    divTitol.innerHTML = `<h2>Modificació dades Espai</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de espai no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEspai', `https://elliot.cat/api/viatges/put/espai=${id}`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Espai</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEspai', 'https://elliot.cat/api/viatges/post/espai', true);
    });
  }

  await auxiliarSelect(data.ciutat_id ?? 0, 'ciutats', 'ciutat_id', 'ciutat');
  await auxiliarSelect(data.img_id ?? 0, 'auxiliarImatgesEspais', 'img_id', 'alt');
  await auxiliarSelect(data.tipus_id ?? 0, 'llistatTipusEspais', 'tipus_id', 'tipus');
}
