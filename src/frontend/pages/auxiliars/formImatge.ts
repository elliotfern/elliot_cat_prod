import { api } from '../../core/api/client';
import { Imatge } from '../../types/Imatge';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formImatge(isUpdate: boolean, id?: string) {
  const form = document.getElementById('uploadImgForm');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnForm') as HTMLButtonElement;

  let data: Partial<Imatge> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Imatge>(`auxiliars/imatges/get/imatgeId`, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades Imatge</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'uploadImgForm', API_URLS.PUT.IMATGE);
    });
  } else {
    divTitol.innerHTML = `<h2>Alta nova imatge</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'uploadImgForm', API_URLS.POST.IMATGE, true);
    });
  }
}
