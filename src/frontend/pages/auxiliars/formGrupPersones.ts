import { api } from '../../core/api/client';
import { GrupPersones } from '../../types/GrupPersona';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formGrupPersones(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formGrupPersones') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnGrupPersones') as HTMLButtonElement | null;

  let data: Partial<GrupPersones> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<GrupPersones>(API_URLS.GET.PERSONES_GRUPS_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació de Grup de persones</h2>`;
    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formGrupPersones', API_URLS.PUT.PERSONES_GRUPS);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Grup de persones</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formGrupPersones', API_URLS.POST.PERSONES_GRUPS, true);
    });
  }
}
