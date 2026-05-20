import { api } from '../../core/api/client';
import { EducacioCvI18n } from '../../types/Curriculum';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formEducacioi18(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formEducacioI18n');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnEducacioi18n') as HTMLButtonElement;

  let data: Partial<EducacioCvI18n> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<EducacioCvI18n>(API_URLS.GET.EDUCACIO_I18N_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació educació i18n del currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEducacioI18n', API_URLS.PUT.EDUCACIO_I18N);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou títol i18n educatiu del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEducacioI18n', API_URLS.POST.EDUCACIO_I18N, true);
    });
  }

  await auxiliarSelect(data.locale ?? 0, 'llengues', 'locale', 'idioma_ca');
  await auxiliarSelect(data.educacio_id ?? 0, 'educacions', 'educacio_id', 'institucio_periode');
}
