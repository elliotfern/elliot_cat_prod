import { api } from '../../core/api/client';
import { Pais } from '../../types/Pais';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formPais(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formPais');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnPais') as HTMLButtonElement;

  let data: Partial<Pais> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Pais>(API_URLS.GET.PAIS_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades País</h2>`;
    btnSubmit.textContent = 'Modificar dades';
    renderFormInputs(data);

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formPais', API_URLS.PUT.PAIS(id));
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou País</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formPais', API_URLS.POST.PAIS, true);
    });
  }
}
