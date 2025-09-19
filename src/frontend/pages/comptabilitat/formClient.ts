import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Fitxa {
  id: number;
  clientNom: string;
  clientCognoms: string | null;
  clientEmail: string | null;
  clientWeb: string | null;
  clientNIF: string | null;
  clientEmpresa: string | null;
  clientAdreca: string | null;
  clientCP: string | null;

  // UUID texto o null
  pais_id: string | null;
  provincia_id: string | null;
  ciutat_id: string | null;

  clientTelefon: string | null;
  clientStatus: number; // 0/1/2...
  clientRegistre?: string | null; // 'YYYY-MM-DD'
}

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;
function nilUuid(u: string | null | undefined): string | null {
  if (u == null) return null; // null o undefined → null
  return ZERO_UUID.test(u) ? null : u; // UUID cero → null; si no, el propio string
}

// Si tu helper `first`:
const first = <T>(d: T | T[] | null | undefined): T | null => (Array.isArray(d) ? d[0] ?? null : d ?? null);

export async function formClient(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formClient') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnClient') as HTMLButtonElement | null;
  if (!divTitol || !btnSubmit || !form) return;

  let record: Partial<Fitxa> = {};

  if (id && isUpdate) {
    // 👇 La API puede devolver data como objeto o array
    const resp = await fetchDataGet<ApiResponse<Fitxa | Fitxa[]>>(API_URLS.GET.CLIENT_ID(id), true);
    const data = first(resp?.data);
    if (!data) return;

    // 🔧 UUID “cero” → null
    data.pais_id = nilUuid(data.pais_id);
    data.provincia_id = nilUuid(data.provincia_id);
    data.ciutat_id = nilUuid(data.ciutat_id);

    record = data;

    divTitol.innerHTML = `<h2>Modificació dades Client</h2>`;
    btnSubmit.textContent = 'Modificar dades';

    // Pinta inputs (no fuerza selects vacíos; auxiliarSelect los preseleccionará)
    renderFormInputs(record);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formClient', API_URLS.PUT.CLIENT);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Client</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formClient', API_URLS.POST.CLIENT, true);
    });
  }

  // --- Selects auxiliares (preselección segura) ---
  await auxiliarSelect(record.pais_id ?? null, 'paisos', 'pais_id', 'pais_ca');
  await auxiliarSelect(record.provincia_id ?? null, 'provincies', 'provincia_id', 'provincia_ca');
  await auxiliarSelect(record.ciutat_id ?? null, 'ciutats', 'ciutat_id', 'ciutat_ca');
  await auxiliarSelect(record.clientStatus ?? null, 'estatsClients', 'clientStatus', 'estat_ca');
}
