import { api } from '../../core/api/client';
import { ExperienciaCv } from '../../types/Curriculum';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formExperiencies(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formCVExperiencia');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnExperiencia') as HTMLButtonElement;

  let data: Partial<ExperienciaCv> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<ExperienciaCv>(API_URLS.GET.EXPERIENCIA_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació experiència professional del currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVExperiencia', API_URLS.PUT.EXPERIENCIA);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova experiència professional del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVExperiencia', API_URLS.POST.EXPERIENCIA, true);
    });
  }

  await auxiliarSelect(data.logo_empresa ?? 0, 'imatgesEmpreses', 'logo_empresa', 'nom');
  await auxiliarSelect(data.empresa_localitzacio ?? 0, 'ciutats', 'empresa_localitzacio', 'ciutat');
}
