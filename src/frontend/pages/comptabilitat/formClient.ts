import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { Client } from '../../types/Client';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';

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

    divTitol.innerHTML = `Client: ${data.nom} ${data.cognoms}`;
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
  await auxiliarSelect(data.contacte_id ?? 0, 'clients', 'contacte_id', 'client');

  await auxiliarSelect(data.estat_id ?? null, 'estatsClients', 'estat_id', 'estat');
}
