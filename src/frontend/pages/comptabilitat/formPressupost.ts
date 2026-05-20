import { api } from '../../core/api/client';
import { Pressupost } from '../../types/Pressupost';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formPressupost(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formPressupost') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnPressupost') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  let data: Partial<Pressupost> = {};

  if (isUpdate && id) {
    try {
      data = await api.get<Pressupost>(`comptabilitat/get/pressupostId`, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `Modificació Pressupost`;
    btnSubmit.textContent = 'Modifica Pressupost';

    renderFormInputs(data);

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
  await auxiliarSelect(data.client_id ?? null, 'clients', 'client_id', 'clientEmpresa');
  await auxiliarSelect(data.servei_id ?? null, 'productes', 'servei_id', 'producte');
  await auxiliarSelect(data.estat_id ?? null, 'estatsClients', 'estat_id', 'estat');
}
