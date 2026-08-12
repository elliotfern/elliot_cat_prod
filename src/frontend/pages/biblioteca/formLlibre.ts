import { api } from '../../core/api/client';
import { Llibre } from '../../types/Llibre';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

type Autor = {
  id: string;
  nom: string;
  autor_nom_complet: string;
};

type Grup = {
  id: string;
  nom: string;
};

type SubTema = {
  id: string;
  sub_tema: string;
  tema: string;
};

let autorsList: Autor[] = [];
let grupsList: Grup[] = [];
let subtemesList: SubTema[] = [];
let temesList: Tema[] = [];

interface Tema {
  id: string;
  tema: string;
}

async function loadTemes() {
  try {
    temesList = await api.get<Tema[]>(`auxiliars/get/temes`);
  } catch (error) {
    console.error('loadAutors failed:', error);

    temesList = [];
  }
}

async function loadAutors() {
  try {
    autorsList = await api.get<Autor[]>(`biblioteca/get/totsAutors`);
  } catch (error) {
    console.error('loadAutors failed:', error);

    autorsList = [];
  }
}

async function loadGrups() {
  try {
    grupsList = await api.get<Grup[]>(`biblioteca/get/totsGrups`);
  } catch (error) {
    console.error('loadGrups failed:', error);

    grupsList = [];
  }
}

async function loadSubTemes() {
  try {
    subtemesList = await api.get<SubTema[]>(`auxiliars/get/subtemes`);
  } catch (error) {
    console.error('loadTemes failed:', error);

    subtemesList = [];
  }
}

async function createGrup(nom: string, slug: string): Promise<Grup | null> {
  try {
    const response = await fetch(`${API_BASE}/biblioteca/post/grupLlibre`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nom: nom.trim(),
        slug: slug.trim(),
      }),
    });

    const result = await response.json();

    console.log('Resposta crear grup:', result);

    if (!response.ok || !result.success) {
      console.error('createGrup failed:', result);
      return null;
    }

    const grup: Grup = {
      id: result.data.id,
      nom: result.data.nom,
    };

    grupsList.push(grup);

    return grup;
  } catch (error) {
    console.error('createGrup failed:', error);

    return null;
  }
}

async function createTema(nom: string, temaId: string): Promise<SubTema | null> {
  try {
    const response = await fetch(`${API_BASE}/adreces/post/subtema`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        sub_tema: nom.trim(),
        tema_id: temaId,
      }),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      console.error('createTema failed:', result);
      return null;
    }

    const subTema: SubTema = {
      id: result.data.id,
      sub_tema: result.data.sub_tema,
      tema: result.data.tema,
    };

    subtemesList.push(subTema);

    return subTema;
  } catch (error) {
    console.error('createTema failed:', error);

    return null;
  }
}

function createTemaSelect(selectedValue: string | null = null) {
  const container = document.getElementById('temaContainer');

  if (!container) return;

  container.innerHTML = '';

  const select = document.createElement('select');

  select.name = 'sub_tema_id';
  select.id = 'sub_tema_id';
  select.className = 'form-select';

  const empty = document.createElement('option');

  empty.value = '';
  empty.textContent = '-- Selecciona tema --';

  select.appendChild(empty);

  for (const subTema of subtemesList) {
    const option = document.createElement('option');

    option.value = String(subTema.id);
    option.textContent = subTema.tema ? `${subTema.sub_tema} (${subTema.tema})` : subTema.sub_tema;

    if (selectedValue && String(selectedValue) === String(subTema.id)) {
      option.selected = true;
    }

    select.appendChild(option);
  }

  container.appendChild(select);
}

function initCreateTemaUI() {
  const container = document.getElementById('temaContainer');

  if (!container) return;

  const buttonsWrapper = document.createElement('div');

  buttonsWrapper.className = 'd-flex gap-2 mt-2';

  const newTemaBtn = document.createElement('button');

  newTemaBtn.type = 'button';
  newTemaBtn.className = 'btn btn-sm btn-secondary';
  newTemaBtn.textContent = '+ Afegir nou sub-tema';

  buttonsWrapper.appendChild(newTemaBtn);

  container.appendChild(buttonsWrapper);

  const formWrapper = document.createElement('div');

  formWrapper.className = 'border rounded p-3 mt-3 mb-3 d-none';

  formWrapper.innerHTML = `
    <div class="mb-3">
      <label for="newTemaNom" class="form-label">
        Nom
      </label>

      <input
        type="text"
        id="newTemaNom"
        class="form-control"
      >
    </div>

    <div class="mb-3">
      <label for="newTemaId" class="form-label">
        Tema
      </label>

      <select
        id="newTemaId"
        class="form-select"
      >
        <option value="">-- Selecciona tema --</option>
      </select>
    </div>

    <button
      type="button"
      id="createTemaBtn"
      class="btn btn-primary"
    >
      Crear sub-tema
    </button>

    <div id="createTemaMessage" class="mt-3"></div>
  `;

  container.appendChild(formWrapper);

  const nomInput = formWrapper.querySelector('#newTemaNom') as HTMLInputElement;

  const temaSelect = formWrapper.querySelector('#newTemaId') as HTMLSelectElement;

  const createBtn = formWrapper.querySelector('#createTemaBtn') as HTMLButtonElement;

  const message = formWrapper.querySelector('#createTemaMessage') as HTMLDivElement;

  for (const tema of temesList) {
    const option = document.createElement('option');

    option.value = String(tema.id);
    option.textContent = tema.tema;

    temaSelect.appendChild(option);
  }

  newTemaBtn.addEventListener('click', () => {
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
    const temaId = temaSelect.value;

    message.innerHTML = '';

    if (!nom || !temaId) {
      message.innerHTML = `
        <div class="alert alert-warning mb-0">
          Cal indicar el nom i el tema.
        </div>
      `;

      return;
    }

    createBtn.disabled = true;

    const subTema = await createTema(nom, temaId);

    createBtn.disabled = false;

    if (!subTema) {
      message.innerHTML = `
        <div class="alert alert-danger mb-0">
          No s’ha pogut crear el sub-tema.
        </div>
      `;

      return;
    }

    const select = document.getElementById('sub_tema_id') as HTMLSelectElement;

    if (select) {
      const option = document.createElement('option');

      option.value = subTema.id;
      option.textContent = subTema.tema ? `${subTema.sub_tema} (${subTema.tema})` : subTema.sub_tema;
      option.selected = true;

      select.appendChild(option);
    }

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        Sub-tema creat correctament.
      </div>
    `;

    nomInput.value = '';
    temaSelect.value = '';

    setTimeout(() => {
      formWrapper.classList.add('d-none');
      message.innerHTML = '';
    }, 1500);
  });
}

function initTestCreateGrupUI() {
  const btnAddGrup = document.getElementById('addGrupBtn');
  const container = document.getElementById('grupsContainer');

  if (!btnAddGrup || !container) return;

  const buttonsWrapper = document.createElement('div');

  buttonsWrapper.className = 'd-flex gap-2 mb-3';

  const newGrupBtn = document.createElement('button');

  newGrupBtn.type = 'button';
  newGrupBtn.className = 'btn btn-sm btn-secondary mt-2';
  newGrupBtn.textContent = '+ Nova col·lecció';

  btnAddGrup.parentElement?.appendChild(newGrupBtn);

  const formWrapper = document.createElement('div');

  formWrapper.className = 'border rounded p-3 mb-3 d-none';

  formWrapper.innerHTML = `
    <div class="mb-3">
      <label for="newGrupNom" class="form-label">
        Nom
      </label>

      <input
        type="text"
        id="newGrupNom"
        class="form-control"
      >
    </div>

    <div class="mb-3">
      <label for="newGrupSlug" class="form-label">
        Slug
      </label>

      <input
        type="text"
        id="newGrupSlug"
        class="form-control"
      >
    </div>

    <button
      type="button"
      id="createGrupBtn"
      class="btn btn-primary"
    >
      Crear col·lecció
    </button>

    <div id="createGrupMessage" class="mt-3"></div>
  `;

  btnAddGrup.parentElement?.insertAdjacentElement('afterend', formWrapper);

  const nomInput = formWrapper.querySelector('#newGrupNom') as HTMLInputElement;

  const slugInput = formWrapper.querySelector('#newGrupSlug') as HTMLInputElement;

  const createBtn = formWrapper.querySelector('#createGrupBtn') as HTMLButtonElement;

  const message = formWrapper.querySelector('#createGrupMessage') as HTMLDivElement;

  newGrupBtn.addEventListener('click', () => {
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
    const slug = slugInput.value.trim();

    message.innerHTML = '';

    if (!nom || !slug) {
      message.innerHTML = `
        <div class="alert alert-warning mb-0">
          Cal indicar el nom i el slug.
        </div>
      `;

      return;
    }

    createBtn.disabled = true;

    const grup = await createGrup(nom, slug);

    if (grup) {
      createGrupSelect(grup.id);
    }

    createBtn.disabled = false;

    if (!grup) {
      message.innerHTML = `
        <div class="alert alert-danger mb-0">
          No s’ha pogut crear la col·lecció.
        </div>
      `;

      return;
    }

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        Col·lecció creada correctament.
      </div>
    `;

    nomInput.value = '';
    slugInput.value = '';

    setTimeout(() => {
      formWrapper.classList.add('d-none');
      message.innerHTML = '';
    }, 1500);
  });
}

function createAuthorSelect(selectedValue: string | null = null) {
  const wrapper = document.createElement('div');

  wrapper.className = 'd-flex gap-2 mb-2';

  const select = document.createElement('select');

  select.name = 'autors[]';
  select.className = 'form-select';

  const empty = document.createElement('option');

  empty.value = '';
  empty.textContent = '-- Selecciona autor --';

  select.appendChild(empty);

  for (const autor of autorsList) {
    const option = document.createElement('option');

    option.value = String(autor.id);
    option.textContent = autor.autor_nom_complet;

    if (selectedValue && String(selectedValue) === String(autor.id)) {
      option.selected = true;
    }

    select.appendChild(option);
  }

  const removeBtn = document.createElement('button');

  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-danger';
  removeBtn.textContent = '✕';

  removeBtn.onclick = () => wrapper.remove();

  wrapper.appendChild(select);
  wrapper.appendChild(removeBtn);

  const container = document.getElementById('autorsContainer');

  container?.appendChild(wrapper);
}

function createGrupSelect(selectedValue: string | null = null) {
  const wrapper = document.createElement('div');

  wrapper.className = 'd-flex gap-2 mb-2';

  const select = document.createElement('select');

  select.name = 'grups[]';
  select.className = 'form-select';

  const empty = document.createElement('option');

  empty.value = '';
  empty.textContent = '-- Selecciona col·lecció --';

  select.appendChild(empty);

  for (const grup of grupsList) {
    const option = document.createElement('option');

    option.value = String(grup.id);
    option.textContent = grup.nom;

    if (selectedValue && String(selectedValue) === String(grup.id)) {
      option.selected = true;
    }

    select.appendChild(option);
  }

  const removeBtn = document.createElement('button');

  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-danger';
  removeBtn.textContent = '✕';

  removeBtn.onclick = () => wrapper.remove();

  wrapper.appendChild(select);
  wrapper.appendChild(removeBtn);

  const container = document.getElementById('grupsContainer');

  container?.appendChild(wrapper);
}

function initAuthorUI() {
  const btn = document.getElementById('addAutorBtn');

  btn?.addEventListener('click', () => {
    createAuthorSelect();
  });
}

function initGrupUI() {
  const btn = document.getElementById('addGrupBtn');

  btn?.addEventListener('click', () => {
    createGrupSelect();
  });

  initTestCreateGrupUI();
}

export async function formLlibre(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formLlibre');

  const divTitol = document.getElementById('titolForm') as HTMLDivElement;

  const btnSubmit = document.getElementById('btn') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  await Promise.all([loadAutors(), loadGrups(), loadSubTemes(), loadTemes()]);

  let data: Partial<Llibre> = {};

  if (id && isUpdate) {
    try {
      data = await api.get<Llibre>(`biblioteca/get/llibreId`, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació dades Llibre</h2>`;

    const btnTornar = document.getElementById('btnTornar') as HTMLAnchorElement;

    if (btnTornar && data.slug) {
      btnTornar.href = `/gestio/biblioteca/fitxa-llibre/${data.slug}`;
    }

    const fileInput = document.getElementById('img_upload') as HTMLInputElement;

    if (fileInput) {
      fileInput.value = '';
    }

    const autorsContainer = document.getElementById('autorsContainer');

    if (autorsContainer) {
      autorsContainer.innerHTML = '';
    }

    const grupsContainer = document.getElementById('grupsContainer');

    if (grupsContainer) {
      grupsContainer.innerHTML = '';
    }

    const temaContainer = document.getElementById('temaContainer');

    if (temaContainer) {
      temaContainer.innerHTML = '';
    }

    initAuthorUI();
    initGrupUI();

    createTemaSelect(data.sub_tema_id ? String(data.sub_tema_id) : null);

    initCreateTemaUI();

    renderFormInputs(data);

    if (data?.autors?.length) {
      for (const autor of data.autors) {
        createAuthorSelect(String(autor.id));
      }
    } else {
      createAuthorSelect();
    }

    if (data?.grups?.length) {
      for (const grup of data.grups) {
        createGrupSelect(String(grup.id));
      }
    } else {
      createGrupSelect();
    }

    btnSubmit.textContent = 'Modificar dades';

    if (!id) {
      console.error('ID de persona no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formLlibre', `${API_BASE}/biblioteca/put/llibre`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Llibre</h2>`;

    btnSubmit.textContent = 'Inserir dades';

    const btnTornar = document.getElementById('btnTornar') as HTMLAnchorElement;

    if (btnTornar) {
      btnTornar.textContent = 'Llistat llibres';
      btnTornar.href = `/gestio/biblioteca/llistat-llibres`;
    }

    const autorsContainer = document.getElementById('autorsContainer');

    if (autorsContainer) {
      autorsContainer.innerHTML = '';
    }

    const grupsContainer = document.getElementById('grupsContainer');

    if (grupsContainer) {
      grupsContainer.innerHTML = '';
    }

    const temaContainer = document.getElementById('temaContainer');

    if (temaContainer) {
      temaContainer.innerHTML = '';
    }

    initAuthorUI();
    initGrupUI();

    createAuthorSelect();
    createGrupSelect();

    createTemaSelect();
    initCreateTemaUI();

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formLlibre', `${API_BASE}/biblioteca/post/llibre`, true);
    });

    form.addEventListener('form:success', (event) => {
      const customEvent = event as CustomEvent;

      const response = customEvent.detail;

      const slug = response?.data?.slug;

      if (!slug) return;

      const btnVeureFitxa = document.getElementById('btnVeureFitxa') as HTMLAnchorElement;

      if (!btnVeureFitxa) return;

      btnVeureFitxa.href = `/gestio/biblioteca/fitxa-llibre/${slug}`;

      btnVeureFitxa.classList.remove('d-none');
    });
  }

  await auxiliarSelect(data.img_id ?? 0, 'imatgesLlibres', 'img_id', 'alt');
  await auxiliarSelect(data.idioma_id ?? 0, 'llengues', 'idioma_id', 'idioma_ca');
  await auxiliarSelect(data.estat_id ?? 0, 'estatLlibre', 'estat_id', 'estat');
  await auxiliarSelect(data.editorial_id ?? 0, 'editorials', 'editorial_id', 'editorial');
  await auxiliarSelect(data.tipus_id ?? 0, 'tipusLlibre', 'tipus_id', 'nomTipus');
}
