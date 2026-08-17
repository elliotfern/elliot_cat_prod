import { Medicament } from '../../types/Medicament';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

export async function formMedicament(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formMedicament');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnMedicament') as HTMLButtonElement;

  let data: Partial<Medicament> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      const response = await fetch(`${API_BASE}/salut/get/medicamentId?id=${id}`);
      const responseData = await response.json();

      if (!responseData || !responseData.data) return;

      data = responseData.data;
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades medicament</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    if (!data.id) {
      console.error('ID de medicament no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formMedicament', `${API_BASE}/salut/put/medicament`, true);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou medicament</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formMedicament', `${API_BASE}/salut/post/medicament`, true);
    });
  }

  await auxiliarSelect(data.facultatiu_id ?? '', 'facultatius', 'facultatiu_id', 'nom');
}
