import { api } from '../../core/api/client';
import { Tema } from '../../types/Tema';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formTema(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formTema');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnTema') as HTMLButtonElement;

  let data: Partial<Tema> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Tema>(API_URLS.GET.TEMA_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació tema</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formTema', API_URLS.PUT.TEMA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou tema</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formTema', API_URLS.POST.TEMA, true);
    });
  }
}
