import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

export async function formFacultatiu(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formFacultatiu');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnFacultatiu') as HTMLButtonElement;

  let data: Partial<Facultatiu> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      const response = await fetch(`${API_BASE}/salut/get/facultatiuId?id=${id}`);
      const responseData = await response.json();

      if (!responseData || !responseData.data) return;

      data = responseData.data;
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades facultatiu</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    if (!data.id) {
      console.error('ID de facultatiu no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formFacultatiu', `${API_BASE}/salut/put/facultatiu`, true);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou facultatiu</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formFacultatiu', `${API_BASE}/salut/post/facultatiu`, true);
    });
  }

  await auxiliarSelect(data.ciutat_id ?? '', 'ciutats', 'ciutat_id', 'ciutat');
}
