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
  viatge_id: string;
  espai_id: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formEspaiVisitat(isUpdate: boolean, slug?: string) {
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
    const response = await fetchDataGet<ApiResponse<Fitxa>>(`https://elliot.cat/api/viatges/get/fitxaEspaiVisitat?espai=${slug}`, true);

    if (!response || !response.data) return;
    data = response.data;
    console.log(data);

    divTitol.innerHTML = `<h2>Modificació dades Espai visitat</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de espai no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEspai', `https://elliot.cat/api/viatges/put/espaiVisitat`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Espai visitat</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEspai', 'https://elliot.cat/api/viatges/post/espaiVisitat', true);
    });
  }

  await auxiliarSelect(data.viatge_id ?? 0, 'viatges', 'viatge_id', 'viatge');
  await auxiliarSelect(data.espai_id ?? 0, 'espais', 'espai_id', 'nom');
}
