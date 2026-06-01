import { api } from '../../core/api/client';
import { Ciutat } from '../../types/Ciutat';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formCiutat(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formCiutat');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnCiutat') as HTMLButtonElement;

  let data: Partial<Ciutat> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Ciutat>(API_URLS.GET.CIUTAT_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades ciutat</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCiutat', API_URLS.PUT.CIUTAT);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova ciutat</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCiutat', API_URLS.POST.CIUTAT, true);
    });
  }

  await auxiliarSelect(data.pais_id ?? 0, 'paisos', 'pais_id', 'pais_ca');
}
