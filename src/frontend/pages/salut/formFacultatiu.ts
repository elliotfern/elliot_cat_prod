import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';
import { Facultatiu } from '../../types/Facultatiu';
import { api } from '../../Infrastructure/Api/Client/ApiClient';

export async function formFacultatiu(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formFacultatiu');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnFacultatiu') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    const facultatiu = await api.get<Facultatiu>(`salut/get/facultatiuId?id=${encodeURIComponent(id)}`);

    if (!facultatiu) return;

    const data = facultatiu;

    divTitol.innerHTML = `<h2>Modificació dades facultatiu</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    if (!data.id) {
      console.error('ID de facultatiu no disponible');
      return;
    }

    await auxiliarSelect(data.ciutat_id ?? '', 'ciutats', 'ciutat_id', 'ciutat');

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formFacultatiu', `${API_BASE}/salut/put/facultatiu`, true);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou facultatiu</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    await auxiliarSelect('', 'ciutats', 'ciutat_id', 'ciutat');

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formFacultatiu', `${API_BASE}/salut/post/facultatiu`, true);
    });
  }
}
