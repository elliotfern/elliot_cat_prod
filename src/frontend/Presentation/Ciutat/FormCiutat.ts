import { Ciutat } from '../../Domain/Ciutat/Entity/Ciutat';
import { GetCiutat } from '../../Application/Ciutat/Get/GetCiutat';
import { CreateCiutat } from '../../Application/Ciutat/Create/CreateCiutat';
import { UpdateCiutat } from '../../Application/Ciutat/Update/UpdateCiutat';
import { CiutatApiRepository } from '../../Infrastructure/Api/Ciutat/CiutatApiRepository';

import { GetPaisos } from '../../Application/Pais/List/GetPaisos';
import { PaisApiRepository } from '../../Infrastructure/Api/Pais/PaisApiRepository';

import { formDataToObject } from '../Utils/formDataToObject';
import { showSuccess, showError } from '../Utils/formMessages';
import { resetForm } from '../Utils/resetForm';
import { renderFormInputs } from '../Utils/renderInputsForm';
import { auxiliarSelectData } from '../Utils/auxiliarSelectData';

export async function formCiutat(isUpdate: boolean, id?: string): Promise<void> {
  const form = document.getElementById('formCiutat') as HTMLFormElement | null;

  const divTitol = document.getElementById('titolForm') as HTMLDivElement | null;

  const btnSubmit = document.getElementById('btnCiutat') as HTMLButtonElement | null;

  if (!form || !divTitol || !btnSubmit) {
    return;
  }

  // ============================================================
  // REPOSITORIES
  // ============================================================

  const repository = new CiutatApiRepository();
  const paisRepository = new PaisApiRepository();

  // ============================================================
  // USE CASES
  // ============================================================

  const getCiutat = new GetCiutat(repository);
  const createCiutat = new CreateCiutat(repository);
  const updateCiutat = new UpdateCiutat(repository);

  const getPaisos = new GetPaisos(paisRepository);

  // ============================================================
  // PAÏSOS
  // ============================================================

  let paisos;

  try {
    paisos = await getPaisos.execute();
  } catch (error) {
    console.error(error);
    return;
  }

  // ============================================================
  // MODIFICAR
  // ============================================================

  if (id && isUpdate) {
    let ciutat: Ciutat;

    try {
      ciutat = await getCiutat.execute(id);
    } catch (error) {
      console.error(error);
      return;
    }

    divTitol.innerHTML = '<h2>Modificació dades ciutat</h2>';

    btnSubmit.textContent = 'Modificar dades';

    renderFormInputs(ciutat);

    auxiliarSelectData(ciutat.pais.id, paisos, 'pais_id', 'pais');

    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      try {
        const data = formDataToObject(form) as Omit<Ciutat, 'id'>;

        await updateCiutat.execute(id, data);

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

  divTitol.innerHTML = '<h2>Creació de nova ciutat</h2>';

  btnSubmit.textContent = 'Inserir dades';

  auxiliarSelectData(null, paisos, 'pais_id', 'pais');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
      const data = formDataToObject(form) as Omit<Ciutat, 'id'>;

      await createCiutat.execute(data);

      showSuccess(form, 'Ciutat creada correctament.');

      resetForm('formCiutat');

      auxiliarSelectData(null, paisos, 'pais_id', 'pais');
    } catch (error: any) {
      showError(form, error, 'Error creant la ciutat.');
    }
  });
}
