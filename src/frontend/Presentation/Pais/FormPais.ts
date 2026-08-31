import { GetPais } from '../../Application/Pais/Get/GetPais';
import { CreatePais } from '../../Application/Pais/Create/CreatePais';
import { UpdatePais } from '../../Application/Pais/Update/UpdatePais';

import { PaisApiRepository } from '../../Infrastructure/Api/Pais/PaisApiRepository';

import { Pais } from '../../Domain/Pais/Entity/Pais';

import { formDataToObject } from '../Utils/formDataToObject';
import { showSuccess, showError } from '../Utils/formMessages';
import { resetForm } from '../Utils/resetForm';
import { renderFormInputs } from '../Utils/renderInputsForm';

export async function formPais(isUpdate: boolean, id?: string): Promise<void> {
  const form = document.getElementById('formPais') as HTMLFormElement | null;

  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;

  const btnSubmit = document.getElementById('btnPais') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) {
    return;
  }

  // ============================================================
  // REPOSITORY
  // ============================================================

  const repository = new PaisApiRepository();

  // ============================================================
  // USE CASES
  // ============================================================

  const getPais = new GetPais(repository);
  const createPais = new CreatePais(repository);
  const updatePais = new UpdatePais(repository);

  // ============================================================
  // MODIFICAR
  // ============================================================

  if (id && isUpdate) {
    let pais: Pais;

    try {
      pais = await getPais.execute(id);
    } catch (error) {
      console.error(error);
      return;
    }

    divTitol.innerHTML = '<h2>Modificació dades País</h2>';

    btnSubmit.textContent = 'Modificar dades';

    renderFormInputs(pais);

    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      try {
        const data = formDataToObject(form) as Omit<Pais, 'id'>;

        await updatePais.execute(id, data);

        showSuccess(form, 'Dades modificades correctament.');
      } catch (error: any) {
        showError(form, error, 'Error modificant les dades.');
      }
    });

    return;
  }

  // ============================================================
  // CREAR
  // ============================================================

  divTitol.innerHTML = '<h2>Creació de nou País</h2>';

  btnSubmit.textContent = 'Inserir dades';

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
      const data = formDataToObject(form) as Omit<Pais, 'id'>;

      await createPais.execute(data);

      showSuccess(form, 'País creat correctament.');

      resetForm('formPais');
    } catch (error: any) {
      showError(form, error, 'Error creant el país.');
    }
  });
}
