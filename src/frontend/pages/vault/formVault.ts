import { Vault } from '../../types/Vault';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

export async function formVault(isUpdate: boolean, idUuid?: string) {
  let data: Partial<Vault> = {};

  const form = document.getElementById('formVault');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnForm') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  if (idUuid && isUpdate) {
    const response = await fetch(`${API_BASE}/vault/get/?serveiId=${idUuid}`);

    const responseData = await response.json();

    if (!responseData || !responseData.data) return;

    data = responseData.data;

    renderFormInputs(data);

    if (!response || !data) return;

    divTitol.innerHTML = `<h2>Modificació dades clau</h2>`;

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de persona no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formVault', `${API_BASE}/vault/put/?clau`, true);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova clau privada</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formVault', `${API_BASE}/vault/post/?clau`, true);
    });
  }

  await auxiliarSelect(data.tipus_id ?? '', 'tipusServeis', 'tipus_id', 'tipus');
}
