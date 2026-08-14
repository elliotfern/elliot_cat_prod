import { api } from '../../core/api/client';
import { Link } from '../../types/Link';
import { transmissioDadesDB } from '../../utils/actualitzarDades';
import { API_URLS } from '../../utils/apiUrls';
import { auxiliarSelect } from '../../utils/auxiliarSelect';
import { renderFormInputs } from '../../utils/renderInputsForm';
import { API_BASE } from '../../utils/urls';

type SubTema = {
  id: string;
  sub_tema: string;
  tema: string;
};

type Idioma = {
  id: string;
  idioma_ca: string;
};

interface Tema {
  id: string;
  tema: string;
}

let temesList: Tema[] = [];

async function loadTemes() {
  try {
    temesList = await api.get<Tema[]>(`auxiliars/get/temes`);
  } catch (error) {
    console.error('loadTemes failed:', error);

    temesList = [];
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

    return {
      id: result.data.id,
      sub_tema: result.data.sub_tema,
      tema: result.data.tema,
    };
  } catch (error) {
    console.error('createTema failed:', error);

    return null;
  }
}

async function createIdioma(idiomaCa: string): Promise<Idioma | null> {
  try {
    const response = await fetch(`${API_BASE}/auxiliars/post/idioma`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        idioma_ca: idiomaCa.trim(),
      }),
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      console.error('createIdioma failed:', result);
      return null;
    }

    return {
      id: result.data.id,
      idioma_ca: result.data.idioma_ca,
    };
  } catch (error) {
    console.error('createIdioma failed:', error);

    return null;
  }
}

function initCreateSubTemaUI() {
  const container = document.getElementById('inputSubTema');

  if (!container) return;

  container.innerHTML = '';

  const newTemaBtn = document.createElement('button');

  newTemaBtn.type = 'button';
  newTemaBtn.className = 'btn btn-sm btn-secondary';
  newTemaBtn.textContent = '+ Afegir nou sub-tema';

  container.appendChild(newTemaBtn);

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

  // Evitar que Enter dins d'aquest mini-formulari faci submit del formulari gran
  formWrapper.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
    }
  });

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

    // Refresca el select via auxiliarSelect: torna a descarregar la llista
    // (ja inclou el nou sub-tema) i reconstrueix Choices amb el nou seleccionat.
    await auxiliarSelect(subTema.id, 'subtemes', 'sub_tema_id', 'sub_tema');

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

function initCreateIdiomaUI() {
  const container = document.getElementById('inputIdioma');

  if (!container) return;

  container.innerHTML = '';

  const newIdiomaBtn = document.createElement('button');

  newIdiomaBtn.type = 'button';
  newIdiomaBtn.className = 'btn btn-sm btn-secondary mt-2';
  newIdiomaBtn.textContent = '+ Afegir idioma';

  container.appendChild(newIdiomaBtn);

  const formWrapper = document.createElement('div');

  formWrapper.className = 'border rounded p-3 mt-2 d-none';

  formWrapper.innerHTML = `
    <div class="mb-3">
      <label for="newIdiomaCa" class="form-label">
        Nom (català)
      </label>

      <input
        type="text"
        id="newIdiomaCa"
        class="form-control"
      >
    </div>

    <button
      type="button"
      id="createIdiomaBtn"
      class="btn btn-primary"
    >
      Crear idioma
    </button>

    <div id="createIdiomaMessage" class="mt-3"></div>
  `;

  container.appendChild(formWrapper);

  // Evitar que Enter dins d'aquest mini-formulari faci submit del formulari gran
  formWrapper.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
    }
  });

  const caInput = formWrapper.querySelector('#newIdiomaCa') as HTMLInputElement;
  const createBtn = formWrapper.querySelector('#createIdiomaBtn') as HTMLButtonElement;
  const message = formWrapper.querySelector('#createIdiomaMessage') as HTMLDivElement;

  newIdiomaBtn.addEventListener('click', () => {
    const isHidden = formWrapper.classList.contains('d-none');

    if (isHidden) {
      formWrapper.classList.remove('d-none');
      caInput.focus();
    } else {
      formWrapper.classList.add('d-none');
    }
  });

  createBtn.addEventListener('click', async () => {
    const idiomaCa = caInput.value.trim();

    message.innerHTML = '';

    if (!idiomaCa) {
      message.innerHTML = `
        <div class="alert alert-warning mb-0">
          Cal indicar el nom de l’idioma.
        </div>
      `;

      return;
    }

    createBtn.disabled = true;

    const idioma = await createIdioma(idiomaCa);

    createBtn.disabled = false;

    if (!idioma) {
      message.innerHTML = `
        <div class="alert alert-danger mb-0">
          No s’ha pogut crear l’idioma.
        </div>
      `;

      return;
    }

    // Mateix truc: recarrega via auxiliarSelect perquè Choices es reconstrueixi bé
    await auxiliarSelect(idioma.id, 'llengues', 'idioma_id', 'idioma_ca');

    message.innerHTML = `
      <div class="alert alert-success mb-0">
        Idioma creat correctament.
      </div>
    `;

    caInput.value = '';

    setTimeout(() => {
      formWrapper.classList.add('d-none');
      message.innerHTML = '';
    }, 1500);
  });
}

export async function formLink(isUpdate: boolean, id?: string) {
  const form = document.getElementById('formLink');
  const divTitol = document.getElementById('titolForm') as HTMLDivElement;
  const btnSubmit = document.getElementById('btnLink') as HTMLButtonElement;

  let data: Partial<Link> = {};

  if (!divTitol || !btnSubmit || !form) return;

  await loadTemes();

  if (id && isUpdate) {
    try {
      data = await api.get<Link>(API_URLS.GET.LINK_ID, {
        id,
      });
    } catch (error) {
      console.error(error);

      return;
    }

    divTitol.innerHTML = `<h2>Modificació enllaç</h2>`;

    renderFormInputs(data);

    btnSubmit.textContent = 'Modificar dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'PUT', 'formLink', API_URLS.PUT.LINK);
    });
  } else {
    divTitol.innerHTML = `<h2>Creació de nou enllaç</h2>`;
    btnSubmit.textContent = 'Inserir dades';

    form.addEventListener('submit', function (event) {
      transmissioDadesDB(event, 'POST', 'formLink', API_URLS.POST.LINK, true);
    });
  }

  initCreateSubTemaUI();
  initCreateIdiomaUI();

  await auxiliarSelect(data.sub_tema_id ?? 0, 'subtemes', 'sub_tema_id', 'sub_tema');
  await auxiliarSelect(data.idioma_id ?? 0, 'llengues', 'idioma_id', 'idioma_ca');
  await auxiliarSelect(data.tipus ?? 0, 'tipusLinks', 'tipus', 'tipus');
}
