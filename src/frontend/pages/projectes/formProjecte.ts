import { api } from '../../core/api/client';
import { ProjecteDetalls } from '../../types/Projecte';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

export async function formProjecte(isUpdate: boolean, id?: number) {
  const form = document.getElementById('formProjecte') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;
  const btnSubmit = document.getElementById('btnProjecte') as HTMLButtonElement | null;

  if (!divTitol || !btnSubmit || !form) return;

  let data: Partial<ProjecteDetalls> = {};

  if (id && isUpdate) {
    try {
      data = await api.get<ProjecteDetalls>(API_URLS.GET.PROJECTE_ID, { id });
    } catch (error) {
      console.error(error);
      return;
    }

    divTitol.innerHTML = `<h2>Modificació del projecte</h2>`;
    btnSubmit.textContent = 'Modificar dades';

    renderFormInputs(data);
  } else {
    divTitol.innerHTML = `<h2>Creació de nou projecte</h2>`;
    btnSubmit.textContent = 'Inserir dades';
  }

  await auxiliarSelect(data.category_id ?? '', 'projectes_categories', 'category_id', 'name');
  await auxiliarSelect(data.client_id ?? '', 'clients', 'client_id', 'client');
  await auxiliarSelect(data.pressupost_id ?? '', 'budgets', 'pressupost_id', 'concepte');
  await auxiliarSelect(data.factura_id ?? '', 'facturesClients', 'factura_id', 'facConcepte');

  // Eliminar cualquier listener anterior
  const oldHandler = (form as any).__projecteSubmitHandler;

  if (oldHandler) {
    form.removeEventListener('submit', oldHandler);
  }

  // Crear un único listener
  const submitHandler = (event: SubmitEvent) => {
    if (isUpdate) {
      transmissioDadesDB(event, 'PUT', 'formProjecte', API_URLS.PUT.PROJECTE);
    } else {
      transmissioDadesDB(event, 'POST', 'formProjecte', API_URLS.POST.PROJECTE, true);
    }
  };

  form.addEventListener('submit', submitHandler);

  // Guardar referencia para poder eliminarlo la próxima vez
  (form as any).__projecteSubmitHandler = submitHandler;
}
