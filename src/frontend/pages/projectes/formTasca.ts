import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { Tasca } from '../../types/Tasca';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';

export async function formTask(isUpdate: boolean, id?: string) {
  let data: Partial<Tasca> = {};

  const form = document.getElementById('taskForm') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLSpanElement | HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProjecte') as HTMLButtonElement | null;

  if (!divTitol || !btnSubmit || !form) return;

  if (id && isUpdate) {
    try {
      data = await api.get<Tasca>(API_URLS.GET.TASK_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    // Título y botón
    if ('innerHTML' in divTitol) {
      (divTitol as HTMLDivElement).innerHTML = `Modificació de la tasca`;
    } else {
      (divTitol as HTMLSpanElement).textContent = `Modificació de la tasca`;
    }
    btnSubmit.textContent = 'Modificar dades';

    // 2) rellenar inputs (ahora ya existen opciones)
    renderFormInputs(data);

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'taskForm', API_URLS.PUT.TASCA);
    });
  } else {
    if ('innerHTML' in divTitol) {
      (divTitol as HTMLDivElement).innerHTML = `Creació de nova tasca`;
    } else {
      (divTitol as HTMLSpanElement).textContent = `Creació de nova tasca`;
    }
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      if (btnSubmit.disabled) return;
      btnSubmit.disabled = true;
      transmissioDadesDB(event, 'POST', 'taskForm', API_URLS.POST.TASCA, true);
      setTimeout(() => (btnSubmit.disabled = false), 2000); // o re-habilitar en callback si tu helper lo soporta
    });
  }

  // 1) cargar selects con preselección
  await auxiliarSelect(data.projecte_id ?? null, 'projectes', 'projecte_id', 'projecte');
  await auxiliarSelect(data.estat ?? '', 'projectes_estats', 'estat', 'estat');
  await auxiliarSelect(data.prioritat ?? '', 'projectes_prioritats', 'prioritat', 'prioritat');
}
