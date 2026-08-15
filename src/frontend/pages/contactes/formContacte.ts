import { Contacte } from '../../types/Contacte';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

export async function formContacte(isUpdate: boolean, idUuid?: string) {
  let data: Partial<Contacte> = {};

  const form = document.getElementById('formContacte');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnForm') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  if (idUuid && isUpdate) {
    const response = await fetch(`${API_BASE}/contactes/get/contacteId?id=${idUuid}`);

    const responseData = await response.json();

    if (!responseData || !responseData.data) return;

    data = responseData.data;

    renderFormInputs(data);

    if (!response || !data) return;

    divTitol.innerHTML = `<h2>Modificació dades contacte</h2>`;

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de persona no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formContacte', `${API_BASE}/contactes/put`, true);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou contacte</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formContacte', `${API_BASE}/contactes/post`, true);
    });
  }

  await auxiliarSelect(data.tipus_id ?? '', 'tipusContacte', 'tipus_id', 'tipus');
  await auxiliarSelect(data.pais_id ?? '', 'paisos', 'pais_id', 'pais_ca');
}
