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

let autorsList: Autor[] = [];

async function loadAutors() {
  try {
    autorsList = await api.get<Autor[]>(`biblioteca/get/totsAutors`);
  } catch (error) {
    console.error('loadAutors failed:', error);

    autorsList = [];
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

function initAuthorUI() {
  const btn = document.getElementById('addAutorBtn');

  btn?.addEventListener('click', () => {
    createAuthorSelect();
  });
}

export async function formLlibre(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formLlibre');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btn') as HTMLButtonElement;

  if (!divTitol || !btnSubmit || !form) return;

  const autorsPromise = loadAutors();

  await autorsPromise;

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

    const container = document.getElementById('autorsContainer');
    if (container) container.innerHTML = '';

    initAuthorUI();
    renderFormInputs(data);

    if (data?.autors?.length) {
      for (const autor of data.autors) {
        createAuthorSelect(String(autor.id));
      }
    } else {
      createAuthorSelect();
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

    const container = document.getElementById('autorsContainer');
    if (container) container.innerHTML = '';

    initAuthorUI();

    // crear primer select vacío
    createAuthorSelect();

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formLlibre', `${API_BASE}/biblioteca/post/llibre`, true);
    });
  }

  await auxiliarSelect(data.img_id ?? 0, 'imatgesLlibres', 'img_id', 'alt');
  await auxiliarSelect(data.sub_tema_id ?? 0, 'subtemes', 'sub_tema_id', 'sub_tema');
  await auxiliarSelect(data.lang ?? 0, 'llengues', 'lang', 'idioma_ca');
  await auxiliarSelect(data.estat_id ?? 0, 'estatLlibre', 'estat_id', 'estat');
  await auxiliarSelect(data.editorial_id ?? 0, 'editorials', 'editorial_id', 'editorial');
  await auxiliarSelect(data.grup ?? 0, 'grupLlibre', 'grup', 'nom');
  await auxiliarSelect(data.tipus_id ?? 0, 'tipusLlibre', 'tipus_id', 'nomTipus');
}
