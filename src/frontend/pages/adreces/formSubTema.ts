import { api } from '../../core/api/client';
import { SubTema } from '../../types/SubTema';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formSubTema(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formSubTema');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnSubTema') as HTMLButtonElement;

  let data: Partial<SubTema> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<SubTema>(API_URLS.GET.SUBTEMA_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació Sub-tema</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formSubTema', API_URLS.PUT.SUBTEMA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Sub-tema</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formSubTema', API_URLS.POST.SUBTEMA, true);
    });
  }

  await auxiliarSelect(data.tema_id ?? 0, 'temes', 'tema_id', 'tema');
}
