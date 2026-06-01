import { api } from '../../core/api/client';
import { Client } from '../../types/Client';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;
function nilUuid(u: string | null | undefined): string | null {
  if (u == null) return null; // null o undefined → null
  return ZERO_UUID.test(u) ? null : u; // UUID cero → null; si no, el propio string
}

// Si tu helper `first`:
const first = <T>(d: T | T[] | null | undefined): T | null => (Array.isArray(d) ? (d[0] ?? null) : (d ?? null));

export async function formClient(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formClient') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLSpanElement | null;
  const btnSubmit = document.getElementById('btnClient') as HTMLButtonElement | null;
  if (!divTitol || !btnSubmit || !form) return;

  let data: Partial<Client> = {};

  if (id && isUpdate) {
    try {
      data = await api.get<Client>(API_URLS.GET.CLIENT_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    // 🔧 UUID “cero” → null
    data.pais_id = nilUuid(data.pais_id);
    data.provincia_id = nilUuid(data.provincia_id);
    data.ciutat_id = nilUuid(data.ciutat_id);

    divTitol.innerHTML = `Client: ${data.clientNom} ${data.clientCognoms}`;
    btnSubmit.textContent = 'Modificar dades';

    // Pinta inputs (no fuerza selects vacíos; auxiliarSelect los preseleccionará)
    renderFormInputs(data);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formClient', API_URLS.PUT.CLIENT);
    });
  } else {
    divTitol.innerHTML = `Nou registre`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formClient', API_URLS.POST.CLIENT, true);
    });
  }

  // --- Selects auxiliares (preselección segura) ---
  await auxiliarSelect(data.pais_id ?? null, 'paisos', 'pais_id', 'pais_ca');
  await auxiliarSelect(data.provincia_id ?? null, 'provincies', 'provincia_id', 'provincia_ca');
  await auxiliarSelect(data.ciutat_id ?? null, 'ciutats', 'ciutat_id', 'ciutat');
  await auxiliarSelect(data.estat_id ?? null, 'estatsClients', 'estat_id', 'estat');
}
