import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { GrupPersones } from '../../types/GrupPersona';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';

export async function formGrupPersones(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formGrupPersones') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnGrupPersones') as HTMLButtonElement | null;

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const data = await api.get<GrupPersones>(API_URLS.GET.PERSONES_GRUPS_ID, {
      id,
    });

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
