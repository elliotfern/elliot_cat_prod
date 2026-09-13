import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';
import { CursHistoria } from '../../types/CursHistoria';

export async function formCursHistoria(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formCurs');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnCurs') as HTMLButtonElement;

  let data: Partial<CursHistoria> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<CursHistoria>(API_URLS.GET.CURS_ID(id));
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació curs Història</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCurs', API_URLS.PUT.CURS_HISTORIA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou curs Història</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCurs', API_URLS.POST.CURS_HISTORIA, true);
    });
  }

  await auxiliarSelect(data.img_id ?? 0, 'imgCursosHistoria', 'img_id', 'alt');
}
