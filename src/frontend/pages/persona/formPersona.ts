import { api } from '../../core/api/client';
import { Persona } from '../../types/Persona';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { setTrixHTML } from '../../utils/setTrix';
import { API_BASE } from '../../utils/urls';

type Pais = {
  id: string;
  pais_ca: string;
};

type Ciutat = {
  id: string;
  ciutat: string;
};

let paisosList: Pais[] = [];

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

    console.log('Resposta crear ciutat:', result);

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

function initCreateCiutatUI() {
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

  // Evitar que Enter dins d'aquest mini-formulari faci submit del formulari gran (excepte al textarea)
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

  // Reutilitza la llista de paisos ja carregada per al select de país de l'autor
  for (const pais of paisosList) {
    const option = document.createElement('option');

    option.value = String(pais.id);
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

    // Refresca els dos selects (naixement i defunció) via auxiliarSelect, preservant
    // el que ja tinguessin seleccionat i sense forçar la ciutat nova en cap.
    const naixementSelect = document.getElementById('ciutat_naixement_id') as HTMLSelectElement | null;
    const defuncioSelect = document.getElementById('ciutat_defuncio_id') as HTMLSelectElement | null;

    await auxiliarSelect(naixementSelect?.value || 0, 'ciutats', 'ciutat_naixement_id', 'ciutat');
    await auxiliarSelect(defuncioSelect?.value || 0, 'ciutats', 'ciutat_defuncio_id', 'ciutat');

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        Ciutat creada correctament. Ja la pots seleccionar al desplegable que correspongui.
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
    }, 2000);
  });
}

async function loadPaisos() {
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

    console.log('Resposta crear país:', result);

    if (!response.ok || !result.success) {
      console.error('createPais failed:', result);
      return null;
    }

    const pais: Pais = {
      id: result.data.id,
      pais_ca: result.data.pais_ca,
    };

    // Es manté a paisosList perquè el mini-formulari de "crear ciutat" té el seu propi
    // select de país (no gestionat per Choices) que es construeix a partir d'aquesta llista.
    paisosList.push(pais);

    return pais;
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

    await auxiliarSelect(pais.id, 'paisos', 'pais_autor_id', 'pais_ca');

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

export async function formPersona(isUpdate: boolean, slug?: string) {
  const form = document.getElementById('formPersona');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnPersona') as HTMLButtonElement;

  let data: Partial<Persona> = {};

  if (!divTitol || !btnSubmit || !form) return;

  if (slug && isUpdate) {
    try {
      data = await api.get<Persona>(`persones/get/persona`, {
        slug,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades Persona</h2>`;

    renderFormInputs(data);

    // Carga robusta en Trix (después de que Trix se haya inicializado)
    await setTrixHTML('descripcio', data.descripcio);

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de persona no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      //transmissioDadesDB(event, 'PUT', 'formPersona', API_URLS.PUT.PERSONA(id));
      transmissioDadesDB(event, 'POST', 'formPersona', `${API_BASE}/persones/put/?persona=${id}`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nova Persona</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      //transmissioDadesDB(event, 'POST', 'formPersona', API_URLS.POST.PERSONA, true);
      transmissioDadesDB(event, 'POST', 'formPersona', `${API_BASE}/persones/post/?persona`, true);
    });
  }

  const grupIds: string[] = Array.isArray(data.grup_ids) ? data.grup_ids : Array.isArray(data.grups) ? data.grups.map((g: { id: string }) => g.id) : [];
  await auxiliarSelect(grupIds, 'grups', 'grup_ids', 'grup_ca');

  await auxiliarSelect(data.img_id ?? 0, 'auxiliarImatgesAutor', 'img_id', 'alt');
  await auxiliarSelect(data.sexe_id ?? 0, 'sexes', 'sexe_id', 'nom');
  await auxiliarSelect(data.dia_naixement ?? 0, 'calendariDies', 'dia_naixement', 'dia');
  await auxiliarSelect(data.dia_defuncio ?? 0, 'calendariDies', 'dia_defuncio', 'dia');
  await auxiliarSelect(data.mes_naixement ?? 0, 'calendariMesos', 'mes_naixement', 'mes');
  await auxiliarSelect(data.mes_defuncio ?? 0, 'calendariMesos', 'mes_defuncio', 'mes');

  await loadPaisos();
  initCreatePaisUI();
  await auxiliarSelect(data.pais_autor_id ?? 0, 'paisos', 'pais_autor_id', 'pais_ca');

  initCreateCiutatUI();
  await auxiliarSelect(data.ciutat_naixement_id ?? 0, 'ciutats', 'ciutat_naixement_id', 'ciutat');
  await auxiliarSelect(data.ciutat_defuncio_id ?? 0, 'ciutats', 'ciutat_defuncio_id', 'ciutat');
}
