import { api } from '../../core/api/client';
import { Espai } from '../../types/Espai';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formEspai(isUpdate: boolean, espai?: string) {
  const form = document.getElementById('formEspai');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnEspai') as HTMLButtonElement;

  let data: Partial<Espai> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (espai && isUpdate) {
    try {
      data = await api.get<Espai>(`viatges/get/fitxaEspai`, {
        espai,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades Espai</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de espai no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formEspai', `https://elliot.cat/api/viatges/put/espai`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Espai</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formEspai', 'https://elliot.cat/api/viatges/post/espai', true);
    });
  }

  await auxiliarSelect(data.ciutat_id ?? 0, 'ciutats', 'ciutat_id', 'ciutat');
  await auxiliarSelect(data.img_id ?? 0, 'auxiliarImatgesEspais', 'img_id', 'alt');
  await auxiliarSelect(data.tipus_id ?? 0, 'llistatTipusEspais', 'tipus_id', 'tipus');
}
