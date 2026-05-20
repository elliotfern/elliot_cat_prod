import { api } from '../../core/api/client';
import { Proveidor } from '../../types/Proveidor';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formProveidor(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formProveidor') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProveidor') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  let data: Partial<Proveidor> = {};

  // Si es actualización, cargamos los datos
  if (isUpdate && id) {
    try {
      data = await api.get<Proveidor>(API_URLS.GET.PROVEIDOR_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació Proveïdor</h2>`;
    btnSubmit.textContent = 'Modificar Proveïdor';

    renderFormInputs(data);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formProveidor', API_URLS.PUT.PROVEIDOR, true);
    });
  } else {
    // Creación
    divTitol.innerHTML = `<h2>Nou Proveïdor</h2>`;
    btnSubmit.textContent = 'Crear Proveïdor';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formProveidor', API_URLS.POST.PROVEIDOR, true);
    });
  }
}
