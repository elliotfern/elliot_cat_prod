import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Despesa {
  id?: number;
  data: string; // 'YYYY-MM-DD'
  concepte: string;
  client_id?: string;
  estat_id: string;
  servei_id: string;
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

const first = <T>(d: T | T[] | null | undefined): T | null => (Array.isArray(d) ? (d[0] ?? null) : (d ?? null));

export async function formPressupost(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formPressupost') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnPressupost') as HTMLButtonElement | null;
  if (!form || !divTitol || !btnSubmit) return;

  let record: Partial<Despesa> = {};

  if (isUpdate && id) {
    const resp = await fetchDataGet<ApiResponse<Despesa | Despesa[]>>(`/api/comptabilitat/get/pressupostId?id=${id}`);
    const data = first(resp?.data);
    if (!data) return;

    record = data;

    divTitol.innerHTML = `Modificació Pressupost`;
    btnSubmit.textContent = 'Modifica Pressupost';

    renderFormInputs(record);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formPressupost', `/api/comptabilitat/put/pressupost`);
    });
  } else {
    divTitol.innerHTML = `Nou Pressupost`;
    btnSubmit.textContent = 'Crear Pressupost';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formPressupost', `/api/comptabilitat/post/pressupost`);
    });
  }

  // --- Selects auxiliares ---
  await auxiliarSelect(record.client_id ?? null, 'clients', 'client_id', 'clientEmpresa');
  await auxiliarSelect(record.servei_id ?? null, 'productes', 'servei_id', 'producte');
  await auxiliarSelect(record.estat_id ?? null, 'estatsClients', 'estat_id', 'estat');
}
