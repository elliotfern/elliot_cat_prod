import { api } from '../../core/api/client';
import { Emissor } from '../../types/Emissor';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formEmissor(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formEmissor') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnEmissor') as HTMLButtonElement | null;

  let data: Partial<Emissor> = {};

  if (!form || !divTitol || !btnSubmit) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Emissor>(API_URLS.GET.EMISSOR_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

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
