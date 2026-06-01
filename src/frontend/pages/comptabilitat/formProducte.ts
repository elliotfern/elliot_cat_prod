import { api } from '../../core/api/client';
import { Producte } from '../../types/Producte';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formProducte(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formProducte') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProducte') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) return;

  let data: Partial<Producte> = {};

  // Si es actualización, cargamos los datos
  if (isUpdate && id) {
    try {
      data = await api.get<Producte>(`comptabilitat/get/pressupostId`, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació Producte</h2>`;
    btnSubmit.textContent = 'Modificar Producte';

    renderFormInputs(data);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formProducte', API_URLS.PUT.PRODUCTE);
    });
  } else {
    // Creación
    divTitol.innerHTML = `<h2>Nou Producte</h2>`;
    btnSubmit.textContent = 'Crear Producte';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formProducte', API_URLS.POST.PRODUCTE, true);
    });
  }
}
