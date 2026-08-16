import { Contacte } from '../../types/Contacte';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

type Pais = {
  id: string;
  pais_ca: string;
};

async function createPais(paisCa: string, paisEn: string): Promise<Pais | null> {
  try {
    const response = await fetch(`${API_BASE}/paisos/post`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        pais_ca: paisCa.trim(),
        pais_en: paisEn.trim(),
      }),
    });

    const result = await response.json();

    console.log('Resposta crear país:', result);

    if (!response.ok || !result.success) {
      console.error('createPais failed:', result);
      return null;
    }

    return {
      id: result.data.id,
      pais_ca: result.data.pais_ca,
    };
  } catch (error) {
    console.error('createPais failed:', error);

    return null;
  }
}

function initCreatePaisUI() {
  const container = document.getElementById('inputPais');

  if (!container) return;

  container.innerHTML = '';

  const newPaisBtn = document.createElement('button');

  newPaisBtn.type = 'button';
  newPaisBtn.className = 'btn btn-sm btn-secondary mt-2';
  newPaisBtn.textContent = '+ Afegir país';

  container.appendChild(newPaisBtn);

  const formWrapper = document.createElement('div');

  formWrapper.className = 'border rounded p-3 mt-2 d-none';

  formWrapper.innerHTML = `
    <div class="mb-3">
      <label for="newPaisCa" class="form-label">
        Nom (català)
      </label>

      <input
        type="text"
        id="newPaisCa"
        class="form-control"
      >
    </div>

    <div class="mb-3">
      <label for="newPaisEn" class="form-label">
        Nom (anglès)
      </label>

      <input
        type="text"
        id="newPaisEn"
        class="form-control"
      >
    </div>

    <button
      type="button"
      id="createPaisBtn"
      class="btn btn-primary"
    >
      Crear país
    </button>

    <div id="createPaisMessage" class="mt-3"></div>
  `;

  container.appendChild(formWrapper);

  // Evitar que Enter dins d'aquest mini-formulari faci submit del formulari gran
  formWrapper.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
    }
  });

  const caInput = formWrapper.querySelector('#newPaisCa') as HTMLInputElement;
  const enInput = formWrapper.querySelector('#newPaisEn') as HTMLInputElement;
  const createBtn = formWrapper.querySelector('#createPaisBtn') as HTMLButtonElement;
  const message = formWrapper.querySelector('#createPaisMessage') as HTMLDivElement;

  newPaisBtn.addEventListener('click', () => {
    const isHidden = formWrapper.classList.contains('d-none');

    if (isHidden) {
      formWrapper.classList.remove('d-none');
      caInput.focus();
    } else {
      formWrapper.classList.add('d-none');
    }
  });

  createBtn.addEventListener('click', async () => {
    const paisCa = caInput.value.trim();
    const paisEn = enInput.value.trim();

    message.innerHTML = '';

    if (!paisCa) {
      message.innerHTML = `
        <div class="alert alert-warning mb-0">
          Cal indicar el nom en català.
        </div>
      `;

      return;
    }

    createBtn.disabled = true;

    const pais = await createPais(paisCa, paisEn);

    createBtn.disabled = false;

    if (!pais) {
      message.innerHTML = `
        <div class="alert alert-danger mb-0">
          No s’ha pogut crear el país.
        </div>
      `;

      return;
    }

    await auxiliarSelect(pais.id, 'paisos', 'pais_id', 'pais_ca');

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        País creat correctament.
      </div>
    `;

    caInput.value = '';
    enInput.value = '';

    setTimeout(() => {
      formWrapper.classList.add('d-none');
      message.innerHTML = '';
    }, 1500);
  });
}

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

  initCreatePaisUI();
  await auxiliarSelect(data.pais_id ?? '', 'paisos', 'pais_id', 'pais_ca');
}
