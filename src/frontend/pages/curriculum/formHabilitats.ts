import { api } from '../../core/api/client';
import { HabilitatCv } from '../../types/Curriculum';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formHabilitats(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formCVHabilitats');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnHabilitat') as HTMLButtonElement;

  let data: Partial<HabilitatCv> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<HabilitatCv>(API_URLS.GET.HABILITAT_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació habilitats currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVHabilitats', API_URLS.PUT.HABILITAT);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova habilitat del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVHabilitats', API_URLS.POST.HABILITAT, true);
    });
  }

  await auxiliarSelect(data.imatge_id ?? 0, 'imatgesIcones', 'imatge_id', 'nom');
}
