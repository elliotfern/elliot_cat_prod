import { api } from '../../core/api/client';
import { EducacioCv } from '../../types/Curriculum';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formEducacio(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formEducacio');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnEducacio') as HTMLButtonElement;

  let data: Partial<EducacioCv> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<EducacioCv>(API_URLS.GET.EDUCACIO_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació educació del currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEducacio', API_URLS.PUT.EDUCACIO_CV_POST);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou títol educatiu del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEducacio', API_URLS.POST.EDUCACIO_CV_POST, true);
    });
  }

  await auxiliarSelect(data.logo_id ?? 0, 'imatgesEmpreses', 'logo_id', 'nom');
  await auxiliarSelect(data.institucio_localitzacio ?? 0, 'ciutats', 'institucio_localitzacio', 'ciutat');
}
