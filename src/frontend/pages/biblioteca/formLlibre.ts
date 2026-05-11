import { fetchDataGet } from '../../services/api/fetchData';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';

interface Fitxa {
  [key: string]: unknown;
  grup_ids?: string[];
  status: string;
  message: string;
  id: string;
  img_id: number;
  sub_tema_id: number;
  lang: number;
  estat_id: number;
  editorial_id: number;
  grup: number;
  tipus_id: number;
  autors: {
    id: string;
    nom: string;
    cognoms: string;
    slug: string;
  }[];

  // --- relaciones
  grups: GrupDTO[];
}

type GrupDTO = { id: string; nom: string };

interface ApiResponse<T> {
  status: string;
  message: string;
  data: T;
}

let autorsList: { id: string; autor_nom_complet: string }[] = [];

async function loadAutors() {
  try {
    const response = await fetch('https://elliot.cat/api/biblioteca/get/totsAutors', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      console.error('Error loading autors:', response.status);
      autorsList = [];
      return;
    }

    const data = await response.json();

    autorsList = Array.isArray(data) ? data : (data?.data ?? []);
  } catch (e) {
    console.error('loadAutors failed:', e);
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

export async function formLlibre(isUpdate: boolean, slug?: string) {
  console.log('FORM INIT');
  const form = document.getElementById('formLlibre');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btn') as HTMLButtonElement;

  let data: Partial<Fitxa> = {
    comarca: 0,
    provincia: 0,
    comunitat: 0,
    estat: 0,
    id: '',
  };

  if (!divTitol || !btnSubmit || !form) return;

  const autorsPromise = loadAutors();
  //const bookPromise = fetchLibro();

  await autorsPromise;
  //await bookPromise;

  if (slug && isUpdate) {
    console.log('ENTER SLUG:', slug, isUpdate);
    const response = await fetch(`https://elliot.cat/api/biblioteca/get/llibreSlug?llibre=${slug}`);
    const data = await response.json();
    console.log(data);

    if (!response || !data) return;

    divTitol.innerHTML = `<h2>Modificació dades Llibre</h2>`;

    const fileInput = document.getElementById('img_upload') as HTMLInputElement;
    if (fileInput) fileInput.value = '';

    renderFormInputs(data);

    const container = document.getElementById('autorsContainer');
    if (container) container.innerHTML = '';

    initAuthorUI();

    if (data?.autors?.length) {
      for (const autor of data.autors) {
        createAuthorSelect(String(autor.id));
      }
    } else {
      createAuthorSelect();
    }

    btnSubmit.textContent = 'Modificar dades';
    const id = (data.id ?? '').toString();

    if (!id) {
      console.error('ID de persona no disponible');
      return;
    }

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formLlibre', `https://elliot.cat/api/biblioteca/put/?llibre`);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou Llibre</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formLlibre', 'https://elliot.cat/api/biblioteca/post/?llibre', true);
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
