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

let autorsList: Autor[] = [];
let grupsList: Grup[] = [];

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

function initTestCreateGrupUI() {
  const btnAddGrup = document.getElementById('addGrupBtn');
  const container = document.getElementById('grupsContainer');

  if (!btnAddGrup || !container) return;

  // Contenedor de botones
  const buttonsWrapper = document.createElement('div');
  buttonsWrapper.className = 'd-flex gap-2 mb-3';

  // Botón Nova col·lecció
  const newGrupBtn = document.createElement('button');
  newGrupBtn.type = 'button';
  newGrupBtn.className = 'btn btn-sm btn-secondary mt-2';
  newGrupBtn.textContent = '+ Nova col·lecció';

  // Insertar al lado de "Afegir col·lecció"
  btnAddGrup.parentElement?.appendChild(newGrupBtn);

  // Contenedor formulario
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

  // El formulario aparece debajo de los botones
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

    // Ocultar formulario después de crear
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

  // placeholder
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

  // placeholder
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

  await Promise.all([loadAutors(), loadGrups()]);

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
    if (fileInput) fileInput.value = '';

    const autorsContainer = document.getElementById('autorsContainer');
    if (autorsContainer) autorsContainer.innerHTML = '';

    const grupsContainer = document.getElementById('grupsContainer');
    if (grupsContainer) grupsContainer.innerHTML = '';

    initAuthorUI();
    initGrupUI();
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
      // Lo mandamos por POST porque PUT no funciona bien con ficheros
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
    if (autorsContainer) autorsContainer.innerHTML = '';

    const grupsContainer = document.getElementById('grupsContainer');
    if (grupsContainer) grupsContainer.innerHTML = '';

    initAuthorUI();
    initGrupUI();

    // crear primer select vacío
    createAuthorSelect();
    createGrupSelect();

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
  await auxiliarSelect(data.sub_tema_id ?? 0, 'subtemes', 'sub_tema_id', 'sub_tema');
  await auxiliarSelect(data.idioma_id ?? 0, 'llengues', 'idioma_id', 'idioma_ca');
  await auxiliarSelect(data.estat_id ?? 0, 'estatLlibre', 'estat_id', 'estat');
  await auxiliarSelect(data.editorial_id ?? 0, 'editorials', 'editorial_id', 'editorial');
  await auxiliarSelect(data.tipus_id ?? 0, 'tipusLlibre', 'tipus_id', 'nomTipus');
}
