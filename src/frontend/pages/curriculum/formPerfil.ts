import { api } from '../../core/api/client';
import { PerfilCV } from '../../types/Curriculum';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formPerfil(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formCVPerfil');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnCVPerfil') as HTMLButtonElement;

  let data: Partial<PerfilCV> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<PerfilCV>(API_URLS.GET.PERFIL_CV_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació perfil currículum</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formCVPerfil', API_URLS.PUT.PERFIL_CV);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou perfil del currículum</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formCVPerfil', API_URLS.POST.PERFIL_CV, true);
    });
  }

  await auxiliarSelect(data.img_perfil ?? 0, 'imatgesUsuaris', 'img_perfil', 'nom');
  await auxiliarSelect(data.localitzacio_ciutat ?? 0, 'ciutats', 'localitzacio_ciutat', 'ciutat');
}
