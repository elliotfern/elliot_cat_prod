import { api } from '../../core/api/client';
import { EspaiVisitat } from '../../types/Espai';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formEspaiVisitat(isUpdate: boolean, espai?: string) {
  const form = document.getElementById('formEspai');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnEspai') as HTMLButtonElement;

  let data: Partial<EspaiVisitat> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (espai && isUpdate) {
    try {
      data = await api.get<EspaiVisitat>(`viatges/get/fitxaEspaiVisitat`, {
        espai,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades Espai visitat</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de espai no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEspai', `https://elliot.cat/api/viatges/put/espaiVisitat`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Espai visitat</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEspai', 'https://elliot.cat/api/viatges/post/espaiVisitat', true);
    });
  }

  await auxiliarSelect(data.viatge_id ?? 0, 'viatges', 'viatge_id', 'viatge');
  await auxiliarSelect(data.espai_id ?? 0, 'espais', 'espai_id', 'nom');
}
