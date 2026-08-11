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
  }

  await auxiliarSelect(data.img_id ?? 0, 'imatgesLlibres', 'img_id', 'alt');
  await auxiliarSelect(data.sub_tema_id ?? 0, 'subtemes', 'sub_tema_id', 'sub_tema');
  await auxiliarSelect(data.idioma_id ?? 0, 'llengues', 'idioma_id', 'idioma_ca');
  await auxiliarSelect(data.estat_id ?? 0, 'estatLlibre', 'estat_id', 'estat');
  await auxiliarSelect(data.editorial_id ?? 0, 'editorials', 'editorial_id', 'editorial');
  await auxiliarSelect(data.tipus_id ?? 0, 'tipusLlibre', 'tipus_id', 'nomTipus');
}
