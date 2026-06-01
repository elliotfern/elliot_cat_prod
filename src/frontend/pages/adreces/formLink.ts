import { api } from '../../core/api/client';
import { Link } from '../../types/Link';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formLink(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formLink');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnLink') as HTMLButtonElement;

  let data: Partial<Link> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Link>(API_URLS.GET.LINK_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació enllaç</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formLink', API_URLS.PUT.LINK);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou enllaç</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formLink', API_URLS.POST.LINK, true);
    });
  }

  await auxiliarSelect(data.sub_tema_id ?? 0, 'subtemes', 'sub_tema_id', 'sub_tema');
  await auxiliarSelect(data.lang ?? 0, 'llengues', 'lang', 'idioma_ca');
  await auxiliarSelect(data.tipus ?? 0, 'tipusLinks', 'tipus', 'tipus');
}
