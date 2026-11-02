import { api } from '../../Infrastructure/Api/Client/ApiClient';
import { Persona } from '../../types/Persona';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../Presentation/Utils/renderInputsForm';
import { setTrixHTML } from '../../utils/setTrix';
import { API_BASE } from '../../utils/urls';
import { Carrec } from '../../types/Carrec';
import Choices from 'choices.js';

type Pais = {
  id: string;
  pais: string;
};

type Ciutat = {
  id: string;
  ciutat: string;
};

type Organitzacio = {
  id: string;
  organitzacio: string;
  sigla: string;
};

let organitzacionsList: Organitzacio[] = [];

async function loadOrganitzacions(): Promise<void> {
  try {
    organitzacionsList = await api.get<Organitzacio[]>('historia/get/llistatOrganitzacions');
  } catch (error) {
    console.error('loadOrganitzacions failed:', error);
    organitzacionsList = [];
  }
}

let paisosList: Pais[] = [];

async function createCiutat(payload: { ciutat: string; descripcio: string; pais_id: string }): Promise<Ciutat | null> {
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
        Nom ciutat
      </label>

      <input
        type="text"
        id="newCiutatNom"
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
  const paisSelect = formWrapper.querySelector('#newCiutatPaisId') as HTMLSelectElement;
  const descripcioInput = formWrapper.querySelector('#newCiutatDescripcio') as HTMLTextAreaElement;
  const createBtn = formWrapper.querySelector('#createCiutatBtn') as HTMLButtonElement;
  const message = formWrapper.querySelector('#createCiutatMessage') as HTMLDivElement;

  // Reutilitza la llista de paisos ja carregada per al select de país de l'autor
  for (const pais of paisosList) {
    const option = document.createElement('option');

    option.value = String(pais.id);
    option.textContent = pais.pais;

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

function createCarrecBlock(carrec?: Carrec): void {
  const container = document.getElementById('carrecsList');

  if (!container) return;

  const index = container.children.length;

  const wrapper = document.createElement('div');

  wrapper.className = 'border rounded p-3 mb-3';

  // ID del càrrec (només existeix quan editem un càrrec)
  if (carrec?.id) {
    const idInput = document.createElement('input');

    idInput.type = 'hidden';
    idInput.name = `carrecs[${index}][id]`;
    idInput.value = carrec.id;

    wrapper.appendChild(idInput);
  }

  // Títol i botó d'eliminació
  const header = document.createElement('div');

  header.className = 'd-flex justify-content-between align-items-center mb-3';

  const title = document.createElement('strong');

  title.textContent = `Càrrec ${index + 1}`;

  const removeBtn = document.createElement('button');

  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-sm btn-danger';
  removeBtn.textContent = '✕';
  removeBtn.setAttribute('aria-label', 'Eliminar càrrec');

  header.appendChild(title);
  header.appendChild(removeBtn);

  wrapper.appendChild(header);

  // Nom del càrrec
  const nomGroup = document.createElement('div');

  nomGroup.className = 'mb-3';

  const nomLabel = document.createElement('label');

  nomLabel.className = 'form-label';
  nomLabel.textContent = 'Nom del càrrec';

  const nomInput = document.createElement('input');

  nomInput.type = 'text';
  nomInput.className = 'form-control';
  nomInput.name = `carrecs[${index}][carrecNom]`;
  nomInput.required = true;
  nomInput.maxLength = 155;
  nomInput.value = carrec?.nom ?? '';

  nomGroup.appendChild(nomLabel);
  nomGroup.appendChild(nomInput);

  wrapper.appendChild(nomGroup);

  // Anys d'inici i finalització
  const yearsRow = document.createElement('div');

  yearsRow.className = 'row g-3 mb-3';

  const iniciGroup = document.createElement('div');

  iniciGroup.className = 'col-md-6';

  const iniciLabel = document.createElement('label');

  iniciLabel.className = 'form-label';
  iniciLabel.textContent = 'Any d’inici';

  const iniciInput = document.createElement('input');

  iniciInput.type = 'number';
  iniciInput.className = 'form-control';
  iniciInput.name = `carrecs[${index}][carrecInici]`;
  iniciInput.min = '1';
  iniciInput.max = '9999';
  iniciInput.step = '1';
  iniciInput.value = carrec?.inici != null ? String(carrec.inici) : '';

  iniciGroup.appendChild(iniciLabel);
  iniciGroup.appendChild(iniciInput);

  const fiGroup = document.createElement('div');

  fiGroup.className = 'col-md-6';

  const fiLabel = document.createElement('label');

  fiLabel.className = 'form-label';
  fiLabel.textContent = 'Any de finalització';

  const fiInput = document.createElement('input');

  fiInput.type = 'number';
  fiInput.className = 'form-control';
  fiInput.name = `carrecs[${index}][carrecFi]`;
  fiInput.min = '1';
  fiInput.max = '9999';
  fiInput.step = '1';
  fiInput.value = carrec?.fi != null ? String(carrec.fi) : '';

  fiGroup.appendChild(fiLabel);
  fiGroup.appendChild(fiInput);

  yearsRow.appendChild(iniciGroup);
  yearsRow.appendChild(fiGroup);

  wrapper.appendChild(yearsRow);

  // Organització
  const organitzacioGroup = document.createElement('div');

  organitzacioGroup.className = 'mb-2';

  const organitzacioLabel = document.createElement('label');

  organitzacioLabel.className = 'form-label';
  organitzacioLabel.textContent = 'Organització';

  const organitzacioSelect = document.createElement('select');

  organitzacioSelect.className = 'form-select';
  organitzacioSelect.name = `carrecs[${index}][organitzacio_id]`;

  const emptyOption = document.createElement('option');

  emptyOption.value = '';
  emptyOption.textContent = '-- Sense organització --';

  organitzacioSelect.appendChild(emptyOption);

  for (const organitzacio of organitzacionsList) {
    const option = document.createElement('option');

    option.value = organitzacio.id;
    option.textContent = organitzacio.sigla ? `${organitzacio.organitzacio} (${organitzacio.sigla})` : organitzacio.organitzacio;

    organitzacioSelect.appendChild(option);
  }

  organitzacioGroup.appendChild(organitzacioLabel);
  organitzacioGroup.appendChild(organitzacioSelect);

  wrapper.appendChild(organitzacioGroup);

  container.appendChild(wrapper);

  // Inicialitzar Choices.js
  const choices = new Choices(organitzacioSelect, {
    searchEnabled: true,
    allowHTML: false,
    shouldSort: false,
    placeholder: true,
    placeholderValue: 'Selecciona una organització:',
    itemSelectText: '',
    noResultsText: 'Sense resultats',
  });

  // Preseleccionar organització existent
  if (carrec?.organitzacio_id) {
    const organitzacioExisteix = organitzacionsList.some((organitzacio) => organitzacio.id === carrec.organitzacio_id);

    if (organitzacioExisteix) {
      choices.setChoiceByValue(carrec.organitzacio_id);
    } else {
      console.warn('No s’ha trobat l’organització del càrrec:', carrec.organitzacio_id);
    }
  }

  // Ajustar amplada de Choices.js
  const choicesContainer = wrapper.querySelector<HTMLElement>('.choices');

  if (choicesContainer) {
    choicesContainer.style.flex = '1';
    choicesContainer.style.minWidth = '0';
  }

  removeBtn.addEventListener('click', () => {
    choices.destroy();
    wrapper.remove();

    actualizarIndicesCarrecs(container);
  });
}

function initCarrecsUI(): void {
  const container = document.getElementById('inputCarrecs');

  if (!container) return;

  container.innerHTML = '';

  const addBtn = document.createElement('button');

  addBtn.type = 'button';
  addBtn.className = 'btn btn-sm btn-secondary mb-3';
  addBtn.textContent = '+ Afegir càrrec';

  const carrecsList = document.createElement('div');

  carrecsList.id = 'carrecsList';

  addBtn.addEventListener('click', () => {
    createCarrecBlock();
  });

  container.appendChild(addBtn);
  container.appendChild(carrecsList);
}

async function createPais(nomPais: string): Promise<Pais | null> {
  try {
    const response = await fetch(`${API_BASE}/paisos/post`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        pais: nomPais.trim(),
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
      pais: result.data.pais,
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
      <label for="newPais" class="form-label">
        Nom País:
      </label>

      <input
        type="text"
        id="newPais"
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

  const paisInput = formWrapper.querySelector('#newPais') as HTMLInputElement;
  const createBtn = formWrapper.querySelector('#createPaisBtn') as HTMLButtonElement;
  const message = formWrapper.querySelector('#createPaisMessage') as HTMLDivElement;

  newPaisBtn.addEventListener('click', () => {
    const isHidden = formWrapper.classList.contains('d-none');

    if (isHidden) {
      formWrapper.classList.remove('d-none');
      paisInput.focus();
    } else {
      formWrapper.classList.add('d-none');
    }
  });

  createBtn.addEventListener('click', async () => {
    const pais = paisInput.value.trim();

    message.innerHTML = '';

    if (!pais) {
      message.innerHTML = `
        <div class="alert alert-warning mb-0">
          Cal indicar el nom en català.
        </div>
      `;

      return;
    }

    createBtn.disabled = true;

    const nomPais = await createPais(pais);

    createBtn.disabled = false;

    if (!nomPais) {
      message.innerHTML = `
        <div class="alert alert-danger mb-0">
          No s’ha pogut crear el país.
        </div>
      `;

      return;
    }

    await auxiliarSelect(nomPais.id, 'paisos', 'pais_autor_id', 'pais');

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        País creat correctament.
      </div>
    `;

    paisInput.value = '';

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
  await auxiliarSelect(data.pais_autor_id ?? 0, 'paisos', 'pais_autor_id', 'pais');

  initCreateCiutatUI();
  await auxiliarSelect(data.ciutat_naixement_id ?? 0, 'ciutats', 'ciutat_naixement_id', 'ciutat');
  await auxiliarSelect(data.ciutat_defuncio_id ?? 0, 'ciutats', 'ciutat_defuncio_id', 'ciutat');

  await loadOrganitzacions();

  initCarrecsUI();

  // Cargar cargos existentes al modificar una persona
  if (Array.isArray(data.carrecs)) {
    for (const carrec of data.carrecs) {
      createCarrecBlock(carrec);
    }
  }
}

function actualizarIndicesCarrecs(container: HTMLElement): void {
  Array.from(container.children).forEach((child, index) => {
    const inputs = child.querySelectorAll<HTMLInputElement | HTMLSelectElement>('input, select');

    inputs.forEach((input) => {
      input.name = input.name.replace(/carrecs\[\d+\]/, `carrecs[${index}]`);
    });

    const heading = child.querySelector('strong');

    if (heading) {
      heading.textContent = `Càrrec ${index + 1}`;
    }
  });
}
