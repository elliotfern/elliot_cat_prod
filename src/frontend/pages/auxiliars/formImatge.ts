import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { Imatge } from '../../types/Imatge';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';

export async function formImatge(isUpdate: boolean, id?: string) {
  const form = document.getElementById('uploadImgForm');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnForm') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const data = await api.get<Imatge>(`auxiliars/imatges/get/imatgeId`, {
      id,
    });

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
