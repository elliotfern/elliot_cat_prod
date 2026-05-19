import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Emissor {
  [key: string]: unknown;
  id: number;
  nom: string;
  nif: string;
  numero_iva?: string;
  pais_id: number;
  adreca?: string;
  telefon?: string;
  email?: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

export async function formEmissor(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formEmissor') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnEmissor') as HTMLButtonElement | null;

  let data: Partial<Emissor> = {
    pais_id: 0,
  };

  if (!form || !divTitol || !btnSubmit) return;

  if (id && isUpdate) {
    // Agafem dades de l'emissor existent
    const response = await fetchDataGet<ApiResponse<Emissor>>(API_URLS.GET.EMISSOR_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `Modificació dades Emissor`;
    btnSubmit.textContent = 'Modificar dades';

    renderFormInputs(data);

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEmissor', API_URLS.PUT.EMISSOR);
    });
  } else {
    // Creació nou emissor
    divTitol.innerHTML = `Creació nou Emissor`;
    btnSubmit.textContent = 'Desar Emissor';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEmissor', API_URLS.POST.EMISSOR, true);
    });
  }

  // Omplir select de països
  await auxiliarSelect(data.pais_id ?? 0, 'paisos', 'pais_id', 'pais_ca');
}
