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
  clientStatus: number;
  pais_id: string | null;
  provincia_id: string | null;
  ciutat_id: string | null;

  ciutat_ca?: string | null;
  pais_ca?: string | null;
  provincia_ca?: string | null;
  estatNom?: string | null;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;

function first<T>(d: T | T[] | null | undefined): T | null {
  if (!d) return null;
  return Array.isArray(d) ? d[0] ?? null : d;
}

function nilUuidToNull(u: string | null | undefined): string | null {
  if (!u) return null;
  return ZERO_UUID.test(u) ? null : u;
}

export async function formClient(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formClient');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnClient') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
  };

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const response = await fetchDataGet<ApiResponse<Fitxa>>(API_URLS.GET.CLIENT_ID(id), true);

    if (!response || !response.data) return;
    data = response.data;

    divTitol.innerHTML = `<h2>Modificació dades Client</h2>`;

    // Normaliza UUIDs "cero" a null
    data.pais_id = nilUuidToNull(data.pais_id);
    data.provincia_id = nilUuidToNull(data.provincia_id);
    data.ciutat_id = nilUuidToNull(data.ciutat_id);

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formClient', API_URLS.PUT.CLIENT);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Client</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formClient', API_URLS.POST.CLIENT, true);
    });
  }

  await auxiliarSelect(data.pais_id ?? null, 'paisos', 'pais_id', 'pais_ca');
  await auxiliarSelect(data.ciutat_id ?? null, 'ciutats', 'ciutat_id', 'ciutat_ca');
  await auxiliarSelect(data.provincia_id ?? null, 'provincies', 'provincia_id', 'provincia_ca');
  await auxiliarSelect(data.clientStatus ?? 0, 'estatsClients', 'clientStatus', 'estat_ca');
}
