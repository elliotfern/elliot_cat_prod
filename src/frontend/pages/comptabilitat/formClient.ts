import { api } from '../../core/api/client';
import { Client } from '../../types/Client';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

const ZERO_UUID = /^0{8}-0{4}-0{4}-0{4}-0{12}$/i;
function nilUuid(u: string | null | undefined): string | null {
  if (u == null) return null; // null o undefined → null
  return ZERO_UUID.test(u) ? null : u; // UUID cero → null; si no, el propio string
}

type Pais = {
  id: string;
  pais_ca: string;
};

type Provincia = {
  id: string;
  provincia_ca: string;
};

type Ciutat = {
  id: string;
  ciutat: string;
};

let paisosList: Pais[] = [];

/**
 * ============================================================
 * CREAR CIUTAT
 * ============================================================
 */

async function createCiutat(payload: { ciutat: string; ciutat_ca: string; ciutat_en: string; descripcio: string; pais_id: string }): Promise<Ciutat | null> {
  try {
    const response = await fetch(`${API_BASE}/ciutats/post`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      console.error('createCiutat failed:', result);
      return null;
    }

    return {
      id: result.data.id,
      ciutat: result.data.ciutat,
    };
  } catch (error) {
    console.error('createCiutat failed:', error);

    return null;
  }
}

function initCreateCiutatUI(): void {
  const container = document.getElementById('inputCiutat');

  if (!container) return;

  container.innerHTML = '';

  const newCiutatBtn = document.createElement('button');

  newCiutatBtn.type = 'button';
  newCiutatBtn.className = 'btn btn-sm btn-secondary mt-2';
  newCiutatBtn.textContent = '+ Afegir ciutat';

  container.appendChild(newCiutatBtn);

  const formWrapper = document.createElement('div');

  formWrapper.className = 'border rounded p-3 mt-2 d-none';

  formWrapper.innerHTML = `
    <div class="mb-3">
      <label for="newCiutatNom" class="form-label">
        Nom original
      </label>

      <input
        type="text"
        id="newCiutatNom"
        class="form-control"
      >
    </div>

    <div class="mb-3">
      <label for="newCiutatCa" class="form-label">
        Nom (català)
      </label>

      <input
        type="text"
        id="newCiutatCa"
        class="form-control"
      >
    </div>

    <div class="mb-3">
      <label for="newCiutatEn" class="form-label">
        Nom (anglès)
      </label>

      <input
        type="text"
        id="newCiutatEn"
        class="form-control"
      >
    </div>

    <div class="mb-3">
      <label for="newCiutatPaisId" class="form-label">
        País
      </label>

      <select
        id="newCiutatPaisId"
        class="form-select"
      >
        <option value="">-- Selecciona país --</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="newCiutatDescripcio" class="form-label">
        Descripció
      </label>

      <textarea
        id="newCiutatDescripcio"
        class="form-control"
        rows="2"
      ></textarea>
    </div>

    <button
      type="button"
      id="createCiutatBtn"
      class="btn btn-primary"
    >
      Crear ciutat
    </button>

    <div id="createCiutatMessage" class="mt-3"></div>
  `;

  container.appendChild(formWrapper);

  formWrapper.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && (e.target as HTMLElement).tagName !== 'TEXTAREA') {
      e.preventDefault();
    }
  });

  const nomInput = formWrapper.querySelector('#newCiutatNom') as HTMLInputElement;

  const caInput = formWrapper.querySelector('#newCiutatCa') as HTMLInputElement;

  const enInput = formWrapper.querySelector('#newCiutatEn') as HTMLInputElement;

  const paisSelect = formWrapper.querySelector('#newCiutatPaisId') as HTMLSelectElement;

  const descripcioInput = formWrapper.querySelector('#newCiutatDescripcio') as HTMLTextAreaElement;

  const createBtn = formWrapper.querySelector('#createCiutatBtn') as HTMLButtonElement;

  const message = formWrapper.querySelector('#createCiutatMessage') as HTMLDivElement;

  // Carregar països al select del mini-formulari
  for (const pais of paisosList) {
    const option = document.createElement('option');

    option.value = pais.id;
    option.textContent = pais.pais_ca;

    paisSelect.appendChild(option);
  }

  newCiutatBtn.addEventListener('click', () => {
    const isHidden = formWrapper.classList.contains('d-none');

    if (isHidden) {
      formWrapper.classList.remove('d-none');
      nomInput.focus();
    } else {
      formWrapper.classList.add('d-none');
    }
  });

  createBtn.addEventListener('click', async () => {
    const nom = nomInput.value.trim();
    const paisId = paisSelect.value;

    message.innerHTML = '';

    if (!nom || !paisId) {
      message.innerHTML = `
        <div class="alert alert-warning mb-0">
          Cal indicar el nom i el país.
        </div>
      `;

      return;
    }

    createBtn.disabled = true;

    const ciutat = await createCiutat({
      ciutat: nom,
      ciutat_ca: caInput.value.trim(),
      ciutat_en: enInput.value.trim(),
      descripcio: descripcioInput.value.trim(),
      pais_id: paisId,
    });

    createBtn.disabled = false;

    if (!ciutat) {
      message.innerHTML = `
        <div class="alert alert-danger mb-0">
          No s’ha pogut crear la ciutat.
        </div>
      `;

      return;
    }

    // Actualitzar el select principal del Client
    await auxiliarSelect(ciutat.id, 'ciutats', 'ciutat_id', 'ciutat');

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        Ciutat creada correctament.
      </div>
    `;

    nomInput.value = '';
    caInput.value = '';
    enInput.value = '';
    descripcioInput.value = '';
    paisSelect.value = '';

    setTimeout(() => {
      formWrapper.classList.add('d-none');
      message.innerHTML = '';
    }, 1500);
  });
}

/**
 * ============================================================
 * CREAR PROVÍNCIA
 * ============================================================
 */

async function createProvincia(provinciaCa: string): Promise<Provincia | null> {
  try {
    const response = await fetch(`${API_BASE}/provincies/post`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        provincia_ca: provinciaCa.trim(),
      }),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      console.error('createProvincia failed:', result);
      return null;
    }

    return {
      id: result.data.id,
      provincia_ca: result.data.provincia_ca,
    };
  } catch (error) {
    console.error('createProvincia failed:', error);

    return null;
  }
}

function initCreateProvinciaUI(): void {
  const container = document.getElementById('inputProvincia');

  if (!container) return;

  container.innerHTML = '';

  const newProvinciaBtn = document.createElement('button');

  newProvinciaBtn.type = 'button';
  newProvinciaBtn.className = 'btn btn-sm btn-secondary mt-2';
  newProvinciaBtn.textContent = '+ Afegir província';

  container.appendChild(newProvinciaBtn);

  const formWrapper = document.createElement('div');

  formWrapper.className = 'border rounded p-3 mt-2 d-none';

  formWrapper.innerHTML = `
    <div class="mb-3">
      <label for="newProvinciaCa" class="form-label">
        Nom (català)
      </label>

      <input
        type="text"
        id="newProvinciaCa"
        class="form-control"
      >
    </div>

    <button
      type="button"
      id="createProvinciaBtn"
      class="btn btn-primary"
    >
      Crear província
    </button>

    <div id="createProvinciaMessage" class="mt-3"></div>
  `;

  container.appendChild(formWrapper);

  formWrapper.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
    }
  });

  const caInput = formWrapper.querySelector('#newProvinciaCa') as HTMLInputElement;

  const createBtn = formWrapper.querySelector('#createProvinciaBtn') as HTMLButtonElement;

  const message = formWrapper.querySelector('#createProvinciaMessage') as HTMLDivElement;

  newProvinciaBtn.addEventListener('click', () => {
    const isHidden = formWrapper.classList.contains('d-none');

    if (isHidden) {
      formWrapper.classList.remove('d-none');
      caInput.focus();
    } else {
      formWrapper.classList.add('d-none');
    }
  });

  createBtn.addEventListener('click', async () => {
    const provinciaCa = caInput.value.trim();

    message.innerHTML = '';

    if (!provinciaCa) {
      message.innerHTML = `
        <div class="alert alert-warning mb-0">
          Cal indicar el nom de la província.
        </div>
      `;

      return;
    }

    createBtn.disabled = true;

    const provincia = await createProvincia(provinciaCa);

    createBtn.disabled = false;

    if (!provincia) {
      message.innerHTML = `
        <div class="alert alert-danger mb-0">
          No s’ha pogut crear la província.
        </div>
      `;

      return;
    }

    // Actualitzar el select principal del Client
    await auxiliarSelect(provincia.id, 'provincies', 'provincia_id', 'provincia_ca');

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        Província creada correctament.
      </div>
    `;

    caInput.value = '';

    setTimeout(() => {
      formWrapper.classList.add('d-none');
      message.innerHTML = '';
    }, 1500);
  });
}

/**
 * ============================================================
 * CREAR PAÍS
 * ============================================================
 */

async function loadPaisos(): Promise<void> {
  try {
    paisosList = await api.get<Pais[]>(`auxiliars/get/paisos`);
  } catch (error) {
    console.error('loadPaisos failed:', error);

    paisosList = [];
  }
}

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

    if (!response.ok || !result.success) {
      console.error('createPais failed:', result);
      return null;
    }

    const pais: Pais = {
      id: result.data.id,
      pais_ca: result.data.pais_ca,
    };

    paisosList.push(pais);

    return pais;
  } catch (error) {
    console.error('createPais failed:', error);

    return null;
  }
}

function initCreatePaisUI(): void {
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

    // Actualitzar el select principal del Client
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

export async function formClient(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formClient') as HTMLFormElement | null;
  const divTitol = document.getElementById('titolForm') as HTMLSpanElement | null;
  const btnSubmit = document.getElementById('btnClient') as HTMLButtonElement | null;
  if (!divTitol || !btnSubmit || !form) return;

  let data: Partial<Client> = {};

  if (id && isUpdate) {
    try {
      data = await api.get<Client>(API_URLS.GET.CLIENT_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    // 🔧 UUID “cero” → null
    data.pais_id = nilUuid(data.pais_id);
    data.provincia_id = nilUuid(data.provincia_id);
    data.ciutat_id = nilUuid(data.ciutat_id);

    divTitol.innerHTML = `Client: ${data.nom} ${data.cognoms}`;
    btnSubmit.textContent = 'Modificar dades';

    // Pinta inputs (no fuerza selects vacíos; auxiliarSelect los preseleccionará)
    renderFormInputs(data);

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'PUT', 'formClient', API_URLS.PUT.CLIENT);
    });
  } else {
    divTitol.innerHTML = `Nou registre`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', (event) => {
      transmissioDadesDB(event, 'POST', 'formClient', API_URLS.POST.CLIENT, true);
    });
  }

  // --- Selects auxiliares (preselección segura) ---
  await loadPaisos();

  initCreatePaisUI();
  await auxiliarSelect(data.pais_id ?? 0, 'paisos', 'pais_id', 'pais_ca');

  initCreateProvinciaUI();
  await auxiliarSelect(data.provincia_id ?? 0, 'provincies', 'provincia_id', 'provincia_ca');

  initCreateCiutatUI();
  await auxiliarSelect(data.ciutat_id ?? 0, 'ciutats', 'ciutat_id', 'ciutat');

  await auxiliarSelect(data.estat_id ?? null, 'estatsClients', 'estat_id', 'estat');
}
